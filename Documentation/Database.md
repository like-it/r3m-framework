        Core::configure($object);
        //mysql connection will be available under attribute in object
        Mysql::connect(
            $object,
            [
                'connection' => BRANCHE::MYSQL,
                'key' => BRANCHE::DATA_MYSQL_KEY
            ]
        );

        //insert example

        $sql = 'INSERT INTO branche VALUES (?, ?, ?, ?, ?, ?, ?);';

        $statement = $object->data(BRANCHE::MYSQL)->prepare($sql);
        $statement->execute([
            Branche::uuid(),
            $object->session('user.uuid'),
            null,
            1000,
            "Priya.software",
            null,
            null
        ]);


        //mysql select example
        $statement = $object->data(BRANCHE::MYSQL)->prepare(BRANCHE::QUERY_GET_NODELIST);
        $statement->execute([0, 20]);

        $nodeList = [];

        while($node = $statement->fetch(MYSQL::DATA_FETCH_OBJECT)){
//             d($node);
        }

//      mysql user upate example
/*
        $sql = "UPDATE user SET uuid = ? WHERE uuid = ?";

        $statement = $object->data(BRANCHE::MYSQL)->prepare($sql);
        $statement->execute([Branche::uuid(), '1123123']);

        $count = $statement->rowCount();

        dd($count);
*/
//         $pdo->prepare($sql)->execute([$name, $id])

        return $nodeList;
//         return $user;

        $stmt = $pdo->prepare("DELETE FROM goods WHERE category = ?");
        $stmt->execute([$cat]);
        $deleted = $stmt->rowCount();