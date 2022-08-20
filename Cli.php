<?php

declare(strict_types=1);

class Cli {


	public static function clearScreen(): void {
		echo "\033c";
	}

	/**
	 * Returns current cursor position in console
	 *
	 * <code>
	 * 	[$x, $y] = getCursor()
	 * </code>
	 *
	 * @return array
	 * @see https://stackoverflow.com/questions/55892416/how-to-get-cursor-position-with-php-cli
	 */
	public static function getCursor(): array {

		if (! self::isCommandExists('stty')) {
			throw new RuntimeException('Can not get cursor position');
		}

		// Save terminal settings
		$ttyProps = trim(shell_exec('stty -g'));

		// Disable canonical input and disable echo
		system('stty -icanon -echo');

		echo "\033[6n";
		$buf = fread(STDIN, 16);

		// Restore terminal settings.
		system("stty '$ttyProps'");

		return sscanf($buf, "\033[%d;%dR");
	}

	public static function setCursor(array $cursorXY): void {
		self::setCursorPosition($cursorXY[0], $cursorXY[1]);
	}

	public static function setCursorPosition(int $xPos, int $yPos): void {
		printf("\033[%d;%dH", $xPos+1, $yPos+1);
	}

	/**
	 * Returns full path to command when command exists,
	 * otherwise will be returned empty string
	 *
	 * @param string $command
	 * @return string
	 */
	protected static function resolve(string $command): string {
		if (0 === stripos(PHP_OS_FAMILY, 'win')) {
			$fp = popen("where $command", 'r');
			$result = fgets($fp, 255);
			pclose($fp);
		}
		else { // non-Windows
			$result = shell_exec("which $command");
		}
		return rtrim((string) $result);
	}

	/**
	 * Check if a shell command exists
	 *
	 * @param mixed $command
	 * @return bool
	 */
	protected static function isCommandExists($command): bool {
		return (bool) self::resolve((string) $command);
	}
}
