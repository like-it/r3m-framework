    <?php
/**
 * @author          Remco van der Velde
 * @since           2023-05-22
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_array_sort(Parse $parse, Data $data, $list=[], $order='asc'){
    if(!strtolower(substr($order, 0, 3)) === 'asc'){
        rsort($list, SORT_NATURAL);
    } else {
        sort($list, SORT_NATURAL);
    }
    return $list;
}
