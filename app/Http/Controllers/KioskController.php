<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Inhabitant;
use App\Household;
use DB;
use Illuminate\Support\Facades\Auth;

class KioskController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
    }
    public function BarangayInfo(){
        return DB::table('barangays')
        ->join('users','users.id','=','barangays.id' )
        ->select('users.name', 
        'barangays.province',
        'barangays.municipality',
        'barangays.region')
        ->where('users.id',Auth::user()->id)
        ->get();
    }
    public function HouseholdsInfo(){
        return HouseHold::selectRaw('Count(id) as "totalhouseholds",
        count(distinct(purok)) as "totalpurok",
        (SELECT count(id) FROM inhabitants where user_id=? and deleted_at IS NULL) as "totalinhabitants"',[Auth::user()->id])
        ->where('user_id',Auth::user()->id)
        ->get();
    }
    public function AgeGroup(){
        $ageroup=array('1-4 years old','5-9 years old','10-14 years olds',
        '15-19 years old','20-24 years old','25-29 years old','30-34 years old',
        '35-39 years old','40-44 years old','45-49 years old','50-54 years old', 
        '55-59 years old','60-64 years old','65-69 years old','70-74 years old',
        '75-79 years old');

        $agerange=array(array(1,4),array(5,9),array(10,14),array(15,19),array(20,24),
        array(25,29),array(30,34),array(35,39),array(40,44),array(45,49),array(50,54),
        array(55,59),array(60,64),array(65,69),array(70,74),array(75,79));


        $id=Auth::user()->id;
        //less than 1
        $query = Inhabitant::leftJoin('users','users.id','=','inhabitants.user_id')
        ->selectRaw('"less than 1" as agegroup,
        count(case when sex="male" then 1 end) as male,
        ((count(case when sex="male" then 1 end)/(SELECT COUNT( * ) from inhabitants WHERE user_id = ?))*100) as malepercent,
        count(case when sex="female" then 1 end) as female,
        ((count(case when sex="female" then 1 end)/(SELECT COUNT( * ) from inhabitants WHERE user_id = ? ))*100) as femalepercent,
        count(*) as total,
        ((count(*)/(SELECT COUNT(*) from inhabitants WHERE user_id = ?))*100) as totalpercent',[Auth::user()->id,Auth::user()->id,Auth::user()->id]
        )
        ->whereRaw("YEAR(CURDATE()) - YEAR(inhabitants.date_of_birth) - IF(STR_TO_DATE(CONCAT(YEAR(CURDATE()),
        '-',
        MONTH(inhabitants.date_of_birth),
        '-',
        DAY(inhabitants.date_of_birth)),
        '%Y-%c-%e') > CURDATE(),
        1,
        0) < 1")
        ->where('users.id', Auth::user()->id);
        //between 1 to 79
        for( $i=0 ; count($ageroup)!=$i ; $i++){
            $querytwo = Inhabitant::leftJoin('users','users.id','=','inhabitants.user_id')
            ->selectRaw('? as agegroup,
            count(case when sex="male" then 1 end) as male,
            ((count(case when sex="male" then 1 end)/(SELECT COUNT( * ) from inhabitants WHERE user_id = ?))*100) as malepercent,
            count(case when sex="female" then 1 end) as female,
            ((count(case when sex="female" then 1 end)/(SELECT COUNT( * ) from inhabitants WHERE user_id = ? ))*100) as femalepercent,
            count(*) as total,
            ((count(*)/(SELECT COUNT( * ) from inhabitants WHERE user_id = ?))*100) as totalpercent',[$ageroup[$i],Auth::user()->id,Auth::user()->id,Auth::user()->id]
            )
            ->whereRaw("YEAR(CURDATE()) - YEAR(inhabitants.date_of_birth) - IF(STR_TO_DATE(CONCAT(YEAR(CURDATE()),
            '-',
            MONTH(inhabitants.date_of_birth),
            '-',
            DAY(inhabitants.date_of_birth)),
            '%Y-%c-%e') > CURDATE(),
            1,
            0) BETWEEN ? AND ?",[$agerange[$i][0],$agerange[$i][1]])
            ->where('users.id', Auth::user()->id);
            $query->union($querytwo);
        }
        //greater than 80
        $querytwo = Inhabitant::leftJoin('users','users.id','=','inhabitants.user_id')
        ->selectRaw('"80 yrs. old above" as agegroup,
        count(case when sex="male" then 1 end) as male,
        ((count(case when sex="male" then 1 end)/(SELECT COUNT( * ) from inhabitants WHERE user_id = ?))*100) as malepercent,
        count(case when sex="female" then 1 end) as female,
        ((count(case when sex="female" then 1 end)/(SELECT COUNT( * ) from inhabitants WHERE user_id = ? ))*100) as femalepercent,
        count(*) as total,
        ((count(*)/(SELECT COUNT( * ) from inhabitants WHERE user_id = ?))*100) as totalpercent',[Auth::user()->id,Auth::user()->id,Auth::user()->id]
        )
        ->whereRaw("YEAR(CURDATE()) - YEAR(inhabitants.date_of_birth) - IF(STR_TO_DATE(CONCAT(YEAR(CURDATE()),
        '-',
        MONTH(inhabitants.date_of_birth),
        '-',
        DAY(inhabitants.date_of_birth)),
        '%Y-%c-%e') > CURDATE(),
        1,
        0) >= 80 ")
        ->where('users.id', Auth::user()->id);
        $query->union($querytwo);
        //total
        $querytwo = Inhabitant::leftJoin('users','users.id','=','inhabitants.user_id')
        ->selectRaw('"total" as agegroup,
        count(case when sex="male" then 1 end) as male,
        ((count(case when sex="male" then 1 end)/(SELECT COUNT( * ) from inhabitants WHERE user_id = ?))*100) as malepercent,
        count(case when sex="female" then 1 end) as female,
        ((count(case when sex="female" then 1 end)/(SELECT COUNT( * ) from inhabitants WHERE user_id = ? ))*100) as femalepercent,
        count(*) as total,
        ((count(*)/(SELECT COUNT( * ) from inhabitants WHERE user_id = ?))*100) as totalpercent',[Auth::user()->id,Auth::user()->id,Auth::user()->id]
        )
        ->where('users.id', Auth::user()->id);
        $query->union($querytwo);

        $query=$query->get();
        return $query;
    }


    public function CivilStatus(){
        //civil status
        $query = Inhabitant::leftJoin('users','users.id','=','inhabitants.user_id')
        ->selectRaw('civil_status,
        count(case when sex="male" then 1 end) as male,
        ((count(case when sex="male" then 1 end)/(SELECT COUNT( * ) from inhabitants WHERE user_id = ?))*100) as malepercent,
        count(case when sex="female" then 1 end) as female,
        ((count(case when sex="female" then 1 end)/(SELECT COUNT( * ) from inhabitants WHERE user_id = ? ))*100) as femalepercent,
        count(*) as total,
        ((count(*)/(SELECT COUNT(*) from inhabitants WHERE user_id = ?))*100) as totalpercent',[Auth::user()->id,Auth::user()->id,Auth::user()->id]
        )
        ->groupBy('civil_status')
        ->where('users.id', Auth::user()->id);
        //total
        $querytwo = Inhabitant::leftJoin('users','users.id','=','inhabitants.user_id')
        ->selectRaw('"total" as civil_status,
        count(case when sex="male" then 1 end) as male,
        ((count(case when sex="male" then 1 end)/(SELECT COUNT( * ) from inhabitants WHERE user_id = ?))*100) as malepercent,
        count(case when sex="female" then 1 end) as female,
        ((count(case when sex="female" then 1 end)/(SELECT COUNT( * ) from inhabitants WHERE user_id = ? ))*100) as femalepercent,
        count(*) as total,
        ((count(*)/(SELECT COUNT( * ) from inhabitants WHERE user_id = ?))*100) as totalpercent',[Auth::user()->id,Auth::user()->id,Auth::user()->id]
        )
        ->where('users.id', Auth::user()->id);
        $query->union($querytwo);

        $query=$query->get();
        return $query;
    }

    public function EthnicGroup(){
        //ethnic group
        $query = Inhabitant::leftJoin('users','users.id','=','inhabitants.user_id')
        ->selectRaw('ethnicgroup,
        count(*) as totalnumber, 
        count(*)/(SELECT COUNT( * ) from inhabitants WHERE user_id = ?)*100 as percent',
        [Auth::user()->id]
        )
        ->groupBy('ethnicgroup')
        ->where('users.id', Auth::user()->id);
        //non-ethnic
        $querytwo = Inhabitant::leftJoin('users','users.id','=','inhabitants.user_id')
        ->selectRaw('"Non-Ethnic" as ethnicgroup,
        count(*) as totalnumber, 
        count(*)/(SELECT COUNT( * ) from inhabitants WHERE user_id = ?)*100 as percent',
        [Auth::user()->id]
        )
        ->where('users.id', Auth::user()->id)
        ->whereNull('ethnicgroup');
        $query->union($querytwo);
        //total
        $querytwo = Inhabitant::leftJoin('users','users.id','=','inhabitants.user_id')
        ->selectRaw('"Total" as ethnicgroup,
        count(*) as totalnumber, 
        count(*)/(SELECT COUNT( * ) from inhabitants WHERE user_id = ?)*100 as percent',
        [Auth::user()->id]
        )
        ->where('users.id', Auth::user()->id);
        $query->union($querytwo);

        $query=$query->get();
        return $query;
    }

    public function FamilySize(){
    }

    public function Religion(){

    }

    
}
