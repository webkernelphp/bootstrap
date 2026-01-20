<?php declare(strict_types=1);

namespace Webkernel\Console;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use function Laravel\Prompts\warning;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\password;

/**
 * Helper wrapper for Laravel Prompts functions
 * Provides type hints and PHPDoc for better IDE support
 */
final class PromptHelper
{
  /**
   * Display a confirmation prompt
   *
   * @param string $label The question to ask
   * @param bool $default Default value
   * @return bool User's response
   */
  public static function confirm(string $label, bool $default = true): bool
  {
    return confirm($label, $default);
  }

  /**
   * Display a selection prompt
   *
   * @param string $label The prompt label
   * @param array<string, string> $options Available options
   * @param string|null $default Default selection
   * @return string Selected value
   */
  public static function select(string $label, array $options, ?string $default = null): string
  {
    return select(label: $label, options: $options, default: $default);
  }

  /**
   * Display a warning message
   *
   * @param string $message Warning message
   * @return void
   */
  public static function warning(string $message): void
  {
    warning($message);
  }

  /**
   * Display an error message
   *
   * @param string $message Error message
   * @return void
   */
  public static function error(string $message): void
  {
    error($message);
  }

  /**
   * Display an info message
   *
   * @param string $message Info message
   * @return void
   */
  public static function info(string $message): void
  {
    info($message);
  }

  /**
   * Execute a callback with a spinner
   *
   * @template T
   * @param callable(): T $callback Callback to execute
   * @param string $message Message to display
   * @return T Result from callback
   */
  public static function spin(callable $callback, string $message): mixed
  {
    return spin($callback, $message);
  }

  /**
   * Display a hidden text input prompt (e.g. for tokens/passwords)
   *
   * @param string $label
   * @param string $placeholder
   * @param bool $required
   * @param callable|null $validate
   * @return string
   */
  public static function textHidden(
    string $label,
    string $placeholder = '',
    bool $required = false,
    ?callable $validate = null,
  ): string {
    return password(label: $label, placeholder: $placeholder, required: $required, validate: $validate);
  }

  /**
   * Display a success message
   *
   * @param string $message Success message
   * @return void
   */
  public static function success(string $message): void
  {
    // Laravel Prompts uses info for general positive feedback,
    // but you can prefix it with a checkmark for clarity.
    info("[SUCCESS] {$message}");
  }
}
