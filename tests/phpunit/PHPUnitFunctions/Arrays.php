<?php
/** 
 * @license http://opensource.org/licenses/MIT MIT
 */


namespace Rundiz\NestedSet\Tests\PHPUnitFunctions;


class Arrays
{


    /**
     * 
     * @link https://www.php.net/manual/en/function.array-diff-assoc.php#V111675 Original source code.
     * @param array $array1
     * @param array $array2
     * @return array
     */
    public static function array_diff_assoc_recursive(array $array1, array $array2): array
    {
        $difference=array();
        foreach($array1 as $key => $value) {
            if( is_array($value) ) {
                if( !isset($array2[$key]) || !is_array($array2[$key]) ) {
                    $difference[$key] = $value;
                } else {
                    $new_diff = static::array_diff_assoc_recursive($value, $array2[$key]);
                    if( !empty($new_diff) )
                        $difference[$key] = $new_diff;
                }
            } else if( !array_key_exists($key,$array2) || $array2[$key] !== $value ) {
                $difference[$key] = $value;
            }
        }
        return $difference;
    }// array_diff_assoc_recursive


}// Arrays
