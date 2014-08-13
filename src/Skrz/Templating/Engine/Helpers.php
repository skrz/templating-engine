<?php
namespace Skrz\Templating\Engine;

/**
 * Utility functions used by templates
 *
 * @author Jakub Kulhan <jakub.kulhan@gmail.com>
 */
class Helpers
{

	/**
	 * @param string $string
	 * @return string
	 */
	public static function escapeJavascript($string)
	{
		return strtr($string, array('\\' => '\\\\', "'" => "\\'", '"' => '\\"', "\r" => '\\r', "\n" => '\\n', '</' => '<\/'));
	}

	/**
	 * @param string $string
	 * @param int $length
	 * @param string $etc
	 * @param bool $break_words
	 * @param bool $middle
	 * @return string
	 */
	public static function truncate($string, $length = 80, $etc = '...', $break_words = false, $middle = false)
	{
		if ($length == 0)
			return '';

		if (function_exists('mb_strlen')) {
			if (mb_strlen($string, 'UTF-8') > $length) {
				$length -= min($length, mb_strlen($etc, 'UTF-8'));
				if (!$break_words && !$middle) {
					$string = preg_replace('/\s+?(\S+)?$/u', '', mb_substr($string, 0, $length + 1, 'UTF-8'));
				}
				if (!$middle) {
					return mb_substr($string, 0, $length, 'UTF-8') . $etc;
				}

				return mb_substr($string, 0, $length / 2, 'UTF-8') . $etc . mb_substr($string, -$length / 2, $length, 'UTF-8');
			}

			return $string;
		}

		// no MBString fallback
		if (isset($string[$length])) {
			$length -= min($length, strlen($etc));
			if (!$break_words && !$middle) {
				$string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length + 1));
			}
			if (!$middle) {
				return substr($string, 0, $length) . $etc;
			}

			return substr($string, 0, $length / 2) . $etc . substr($string, -$length / 2);
		}

		return $string;
	}

	/**
	 * @param string $string
	 * @param string $encoding
	 * @return string
	 */
	public static function ucfirst($string, $encoding = "UTF-8")
	{
		return mb_strtoupper(mb_substr($string, 0, 1, $encoding), $encoding) . mb_substr($string, 1, mb_strlen($string, $encoding), $encoding);
	}

	/**
	 * @param \DateTime|string|int $date
	 * @param string $format
	 * @return string
	 * @throws TemplateException
	 */
	public static function dateFormat($date, $format)
	{
		if (is_null($date)) {
			return null;

		} elseif (is_numeric($date)) {
			$date = new \DateTime("@$date");

		} elseif (is_string($date)) {
			$date = new \DateTime($date);

		} elseif (!($date instanceof \DateTime)) {
			throw new TemplateException(
				"Unsupported date of type " . gettype($date) .
				(is_object($date) ? " of class " . get_class($date) : "") .
				"."
			);
		}

		return $date->format($format);
	}

} 