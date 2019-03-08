<?php 	
	include_once "PorterStemmer.php";
	class PhrasePorterStemmer
	{
		public static function StemPhrase($phrase)
        {
			$array = explode(' ', $phrase);
            foreach ($array as &$word)
			{
				$word = PorterStemmer::Stem($word);
			}
			return $array;
        }
		
		public static function StemArray($phrase_array)
        {
            foreach ($phrase_array as &$words)
			{
				$words = implode(' ', array_map('PorterStemmer::Stem',explode(' ', $words)));
			}
			return $phrase_array;
        }
	}
	/*$array = array ('computer','computers','computes');
	print_r(PhrasePorterStemmer::StemArray($array));*/
?>