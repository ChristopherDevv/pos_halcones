<?php 

namespace App\Enums;

use BenSampo\Enum\Enum;
use Illuminate\Validation\Rules\Enum as RulesEnum;

final class ComboType extends RulesEnum
{
    /* 
    * Tipos de combos
    */
    public static $FALKOMBO = ['palomitas'];
    
    public static $THREE_POINTER = ['palomitas', 'nachos'];

    public static $PICK_AND_ROLL = ['hotdogs'];

    public static $MVP = ['palomitas', 'nachos', 'hotdogs'];

}