<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;
use App\Stop_words;
use App\PorterStemmer;

include_once "PorterStemmer.php";

	class PhrasePorterStemmer extends Model
	{
		public static function StemPhrase($phrase)
        {
			$array = explode(' ', $phrase);
            foreach ($array as &$word)
			{
                $porterStemmer = new PorterStemmer();
				$word = $porterStemmer->Stem($word);
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
