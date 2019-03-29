<?php
$response = array(
    'flash' => $this->Session->flash(),
    'result' => $result
);
echo json_encode($response);