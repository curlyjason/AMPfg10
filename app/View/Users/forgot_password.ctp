<?php
$response = array(
    'flash' => $this->Flash->render(),
    'result' => $result
);
echo json_encode($response);