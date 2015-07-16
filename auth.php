<?php
    $tryauth = $_POST['tryauth'];
    if (!empty($tryauth)) {
        $AUTHKEY = 'orddrrmu6';

        if ($tryauth == $AUTHKEY) {
            echo '{"success":true}';
        }
        else {
            echo '{"success":false}';
        }
    }
?>