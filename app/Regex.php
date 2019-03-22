<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;

class Regex extends Model {

// ------------------------

    public $regex_Name = '/^[A-Z]+(([\',. -][a-zA-Z ])?[a-zA-Z]*)*$/';
//    any word have capital letter in the first and it accept the name that have ',.-


// ------------------------




// ------------------------

    public $regex_Link = '/^(^|\s)((https?:\/\/)?[\w-]+(\.[a-z-]+)+\.?(:\d+)?(\/\S*)?)$/';
//    bit.ly/00a0sa0sasaxsdd
//    http://bit.ly/00a0sa0sasaxsdd
//    https://bit.ly/00a0sa0sasaxsdd
//    http://www.terra.es/asasa
//    https://www.terra.es/asasa
//    https://www.terra.es.com/asasas
//    https://www.terra2.es.com/asasas

// ------------------------



// ------------------------

    public $regex_honorifics = '/^(Master|Mr|Miss|Mrs|Ms|Mx|Dr|Professor|QC)$/';
//    Master
//    Mr
//    Miss
//    Mrs
//    Ms
//    Mx
//    Dr
//    Professor
//    QC
//    Professor

// ------------------------



// ------------------------

    public $regex_date = "/^([12]\d{3}[\',. -](((0[1-9]|1[0-2]|[1-9])|([J|j]an|[J|j]anuary|[F|f]eb|[F|f]ebruary|[M|m]ar|[M|m]arch|[A|a]pr|[A|a]pril|[M|m]ay|[J|j]un|[J|j]une|[J|j]ul|[J|j]uly|[A|a]ug|[A|a]ugust|[S|s]ep|[S|s]eptember|[O|o]ct|[O|o]ctober|[N|n]ov|[N|n]ovember|[D|d]ec|[D|d]ecember))[\',. -](0[1-9]|[12]\d|3[01]))|((0[1-9]|[12]\d|3[01])[\',. -]((0[1-9]|1[0-2]|[1-9])|([J|j]an|[J|j]anuary|[F|f]eb|[F|f]ebruary|[M|m]ar|[M|m]arch|[A|a]pr|[A|a]pril|[M|m]ay|[J|j]un|[J|j]une|[J|j]ul|[J|j]uly|[A|a]ug|[A|a]ugust|[S|s]ep|[S|s]eptember|[O|o]ct|[O|o]ctober|[N|n]ov|[N|n]ovember|[D|d]ec|[D|d]ecember))))|((((0[1-9]|1[0-2]|[1-9])|([J|j]an|[J|j]anuary|[F|f]eb|[F|f]ebruary|[M|m]ar|[M|m]arch|[A|a]pr|[A|a]pril|[M|m]ay|[J|j]un|[J|j]une|[J|j]ul|[J|j]uly|[A|a]ug|[A|a]ugust|[S|s]ep|[S|s]eptember|[O|o]ct|[O|o]ctober|[N|n]ov|[N|n]ovember|[D|d]ec|[D|d]ecember))[\',. -](0[1-9]|[12]\d|3[01]))|((0[1-9]|[12]\d|3[01])[\',. -]((0[1-9]|1[0-2]|[1-9])|([J|j]an|[J|j]anuary|[F|f]eb|[F|f]ebruary|[M|m]ar|[M|m]arch|[A|a]pr|[A|a]pril|[M|m]ay|[J|j]un|[J|j]une|[J|j]ul|[J|j]uly|[A|a]ug|[A|a]ugust|[S|s]ep|[S|s]eptember|[O|o]ct|[O|o]ctober|[N|n]ov|[N|n]ovember|[D|d]ec|[D|d]ecember)))[\',. -][12]\d{3})$/";
//    2000-01-01
//    2000 01 01
//    01 01 2000
//    01 january 2000


// ------------------------

    public $regex_Year = "/^[12]\d{3}$/";



// ------------------------



// ------------------------

    public $regex_Month = "/^((0[1-9]|1[0-2]|[1-9])|([J|j]an|[J|j]anuary|[F|f]eb|[F|f]ebruary|[M|m]ar|[M|m]arch|[A|a]pr|[A|a]pril|[M|m]ay|[J|j]un|[J|j]une|[J|j]ul|[J|j]uly|[A|a]ug|[A|a]ugust|[S|s]ep|[S|s]eptember|[O|o]ct|[O|o]ctober|[N|n]ov|[N|n]ovember|[D|d]ec|[D|d]ecember))$/";




// ------------------------



// ------------------------

    public $regex_Day = "/^(0[1-9]|[12]\d|3[01])$/";




// ------------------------



// ------------------------










    function __construct(){

    }

    public function isName($term){
        return preg_match($this->regex_Name, $term);
    }


    public function isLink($term){
        return preg_match($this->regex_Link, $term);
    }
    public function getGeneralLink($term){
        return $term;
    }


    public function ishonorifics($term){
        return preg_match($this->regex_honorifics, $term);
    }


    public function isDate($term){
        return preg_match($this->regex_date, $term);
    }
    public function isYear($term){
        return preg_match($this->regex_Year, $term);
    }
    public function isMonth($term){
        return preg_match($this->regex_Month, $term);
    }
    public function isDay($term){
        return preg_match($this->regex_Day, $term);
    }
    public function getGeneralDate($term){
        //var_dump($term);
        return date("m-d-Y", strtotime($term));
    }

}

?>
