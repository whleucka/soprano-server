<?php
function dump($o)
{
    echo "<pre style='overflow: auto; padding: 20px; background-color: #fbfbfb; border: 2px dashed darkred;'>";
    echo "<strong>DUMP</strong><br><br>";
    print_r($o);
    echo "</pre>";
}
