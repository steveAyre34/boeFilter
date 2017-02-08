<?php
/**
 * Created by JetBrains PhpStorm.
 * User: jimbo
 * Date: 7/18/13
 * Time: 3:24 PM
 * To change this template use File | Settings | File Templates.
 */


require_once($_SERVER['DOCUMENT_ROOT'] .'/common.php');

$sql = "SHOW TABLES FROM boe LIKE '%_verified'";
$result = mysqli_query($link, $sql);

if (!$result) {
    echo "DB Error, could not list tables\n";
    echo 'MySQL Error: ' . mysqli_error($link);
    exit;
}

$tables = array();
while ($row = $result->fetch_row()) {
  //  echo "Table: {$row[0]}\n";
    $tables[] = $row[0];

}
$result->free();
gc_enable();
foreach ($tables as $t)
{
    gc_collect_cycles ( );
    set_time_limit(120);

    $sql = 'SELECT * FROM '.$t;

    /* Select queries return a resultset */
    if ($result = mysqli_query($link, $sql))
    {
        printf("County $t returned %d rows.\n", $result->num_rows);
        ini_set('memory_limit','4024M');

        $addresses = array();
        while ($a = $result->fetch_object())
        {
            $addresses[] = $a;
        }
        // Cleanup result
        $result->free();
        echo "Sanity check of addresses in $t";
        echo count($addresses);
        flush();
        foreach ($addresses as $a)
        {
            set_time_limit(10);
            $zip4 = $a->zip4;
            if (strlen($zip4) <= 5)
            {
                continue;
            }
            if (strpos('-',$zip4))
            {
                continue;
            }
            $newzip = substr($zip4,0,5).'-'.substr($zip4,5);
          //  echo "replace $zip with $newzip<br/>";

            $sql  = "UPDATE {$c}_verified";
            $sql .= " SET zip4 = '{$zip4}' ";
            $sql .= " WHERE voterid='{$a->voterid}'";
            mysqli_query($link, $sql);
        }
        /* free result set */
        $result->close();
    }
}




