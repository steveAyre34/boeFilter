<?php
/**
 * Created by JetBrains PhpStorm.
 * User: jimbo
 * Date: 7/18/13
 * Time: 3:24 PM
 * To change this template use File | Settings | File Templates.
 */


require_once($_SERVER['DOCUMENT_ROOT'] .'/common.php');

$counties = array(
    'ulster',
    'orange',
    'rockland',
    'dutchess',
    'columbia',
    'chautauqua'
);

foreach ($counties as $c)
{
    set_time_limit(60);

    $sql = 'SELECT * FROM '.$c.'_verified';

    /* Select queries return a resultset */
    if ($result = mysqli_query($link, $sql))
    {
        printf("County $c returned %d rows.\n", $result->num_rows);
        ini_set('memory_limit','1024M');

       $addresses = array();
        while ($a = $result->fetch_object())
        {
            $addresses[] = $a;
        }
        // Cleanup result
        $result->free();
        echo "Sanity check of addresses in $c";
        echo count($addresses);
        flush();
        foreach ($addresses as $a)
        {
            set_time_limit(10);
            $sql  = "UPDATE {$c}_verified";
            $sql .= " SET crrt = '{$a->dp3}', dp3 = '{$a->crrt}' ";
            $sql .= " WHERE voterid='{$a->voterid}'";
            mysqli_query($link, $sql);
        }
        /* free result set */
        $result->close();
    }



}
