<?php
abstract class StringUtils {
	
	public static function removeSpace($str) {
		$result = "";
		$pieces = explode(" ", $str);

		foreach ($pieces as $piece) {
			if(trim($piece) != "") {
				$result .= $piece;
			}
		}
		
		return $result;
	}

	public  static function removeChar($originalStr, $char) {
		$newStr = str_replace($char, "", $originalStr);
		return $newStr;
	}
	
	public  static function removeChars($originalStr, $chars) {
		if(is_array($chars)) {
			$newStr = $originalStr;
			foreach ($chars as $char) {
				$newStr = self::removeChar($newStr, $char);
			}
			return $newStr;
		}
		else 
			return self::removeChar($originalStr, $char);
	}
}
?>