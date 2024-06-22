<?php

namespace App\Service;

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

class FirebaseRealtime
{
    protected $database;

    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(base_path('/firebase/kumande-64a66-firebase-adminsdk-maclr-55c5b66363.json'));
        $firebase = $factory->withDatabaseUri('https://kumande-64a66-default-rtdb.firebaseio.com/');

        $this->database = $firebase->createDatabase();
    }

    public function insert_command($path, $data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $reference = $this->database->getReference($path);
        $reference->set($data);
    }
}
