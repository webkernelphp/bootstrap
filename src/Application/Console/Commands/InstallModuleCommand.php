<?php declare(strict_types=1);
namespace Webkernel\Console\Commands;

use Illuminate\Console\Command;
use Webkernel\Modules\Services\ModuleService;
use Webkernel\Modules\Managers\{LockManager, BackupManager, ComposerManager};
use Webkernel\Modules\Core\{WebKernelModuleValidator, ConfigManager};
use Webkernel\Modules\Hooks\HookExecutor;
use Webkernel\Modules\Providers\{GitHubProvider, WebKernelProvider};
use Webkernel\Console\PromptHelper;

final class InstallModuleCommand extends Command
{
  protected $signature = 'webkernel:install
                            {source : Module source (owner/repo, wk://module, etc)}
                            {--with-version= : Specific version to install}
                            {--latest : Install latest version}
                            {--token= : Authentication token (manual override)}
                            {--save-token : Save token to config}
                            {--no-backup : Skip backup creation}
                            {--no-hooks : Skip hook execution}
                            {--no-validate : Skip validation}
                            {--insecure : Disable SSL verification}
                            {--pre-release : Include pre-releases}
                            {--dry-run : Simulate without making changes}';

  protected $description = 'Install a WebKernel module from a source provider (supports private GitHub repos)';

  public function handle(): int
  {
    $identifier = (string) $this->argument('source');
    $config = new ConfigManager();
    $manualToken = $this->option('token') ? (string) $this->option('token') : null;

    if ($manualToken && $this->option('save-token')) {
      $owner = $this->extractOwnerFromSource($identifier);
      if ($owner) {
        $config->saveGithubToken($owner, $manualToken);
        $this->components->info("Token for '{$owner}' saved successfully");
      }
    }

    if ($this->option('insecure')) {
      $this->showInsecureWarning();
    }

    $createBackup = !$this->option('no-backup') && PromptHelper::confirm('Create backup before installation?', true);

    // Create provider for fetching releases
    $provider = $this->createProvider($identifier, $manualToken);

    $releases = PromptHelper::spin(
      fn() => $provider->fetchReleases($identifier, (bool) $this->option('pre-release')),
      "Fetching releases for {$identifier}...",
    );

    if (!$releases || count($releases) === 0) {
      PromptHelper::error('No releases found');
      return 1;
    }

    $version = $this->selectVersion($releases);
    if (!$version) {
      PromptHelper::error('No version selected');
      return 1;
    }

    // Get the token from the provider (it may have been set during fetchReleases)
    $providerToken = $provider->getToken();

    // Create service with the token from the provider
    $service = $this->createService($providerToken);

    $result = PromptHelper::spin(
      fn() => $service->installModule($identifier, $version, $createBackup),
      "Installing {$identifier} version {$version}...",
    );

    if ($result['success']) {
      if (isset($result['dry_run']) && $result['dry_run']) {
        PromptHelper::info('[DRY RUN] Would have installed module');
        return 0;
      }

      $this->newLine();
      $this->components->info('Module installed successfully!');
      $this->table(
        ['Property', 'Value'],
        [
          ['Path', $result['path'] ?? 'N/A'],
          ['Version', $result['version'] ?? 'N/A'],
          ['Namespace', $result['namespace'] ?? 'N/A'],
          ['Install Path', $result['install_path'] ?? 'N/A'],
        ],
      );

      $this->newLine();
      $this->components->info('Run the following to rebuild the manifest:');
      $this->line('  php bootstrap/Application/Arcanes/BuildManifest.php');
      return 0;
    }

    PromptHelper::error($result['error'] ?? 'Installation failed');
    return 1;
  }

  private function extractOwnerFromSource(string $source): ?string
  {
    if (preg_match('#github\.com/([^/]+)/#i', $source, $m)) {
      return $m[1];
    }
    if (preg_match('#^([^/]+)/#', $source, $m)) {
      return $m[1];
    }
    return null;
  }

  private function selectVersion(array $releases): ?string
  {
    if ($this->option('with-version')) {
      return (string) $this->option('with-version');
    }

    if ($this->option('latest')) {
      return (string) ($releases[0]['tag_name'] ?? null);
    }

    $options = [];
    foreach ($releases as $release) {
      $tag = (string) ($release['tag_name'] ?? '');
      if ($tag === '') {
        continue;
      }

      $prerelease = $release['prerelease'] ?? false ? ' [PRE-RELEASE]' : '';
      $published = (string) ($release['published_at'] ?? '');
      $published = $published !== '' ? substr($published, 0, 10) : '';
      $name = (string) ($release['name'] ?? '');
      $label = sprintf('%s - %s (%s)%s', $tag, $name, $published, $prerelease);
      $options[$tag] = $label;
    }

    if ($options === []) {
      return null;
    }

    $default = array_key_first($options);
    return PromptHelper::select('Select release to install', $options, $default);
  }

  private function showInsecureWarning(): void
  {
    PromptHelper::warning('SSL verification disabled - INSECURE MODE ACTIVE');
    PromptHelper::warning('This exposes you to man-in-the-middle attacks');
    PromptHelper::warning('DO NOT USE IN PRODUCTION');

    if (!PromptHelper::confirm('Continue anyway?', false)) {
      PromptHelper::info('Operation cancelled');
      exit(0);
    }
  }

  private function createProvider(string $identifier, ?string $token): GitHubProvider|WebKernelProvider
  {
    $insecure = (bool) $this->option('insecure');

    if (str_contains($identifier, 'webkernelphp.com') || str_starts_with($identifier, 'wk://')) {
      return new WebKernelProvider($token);
    }

    return new GitHubProvider($token, $insecure);
  }

  private function createService(?string $token = null): ModuleService
  {
    $lock = new LockManager();
    $backup = new BackupManager();
    $composer = new ComposerManager();
    $validator = new WebKernelModuleValidator();
    $hookExecutor = new HookExecutor();

    $service = new ModuleService($lock, $backup, $composer, $validator, $hookExecutor);

    $insecure = (bool) $this->option('insecure');
    $service->addProvider(new GitHubProvider($token, $insecure));
    $service->addProvider(new WebKernelProvider($token));

    if ($this->option('no-hooks')) {
      $service->setExecuteHooks(false);
    }

    if ($this->option('no-validate')) {
      $service->setValidateModules(false);
    }

    if ($this->option('dry-run')) {
      $service->setDryRun(true);
    }

    return $service;
  }
}
