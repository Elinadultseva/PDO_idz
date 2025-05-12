<?php
$dsn = "mysql:host=localhost;dbname=lb_pdo_goods;charset=utf8";
$user = 'root';
$pass = '';

$vendorName = $_GET["vendorName"] ?? null;
$categoryName = $_GET["categoryName"] ?? null;
$pricerange = $_GET["pricerange"] ?? null;

try {
    
    $dbh = new PDO($dsn, $user, $pass);
    
    $logDb = new PDO("sqlite:log.db");
    

    if ($vendorName) {
        $logQuery = $logDb->prepare("INSERT INTO log (query_type, param1) VALUES ('vendor', :param1)");
        $logQuery->execute([':param1' => $vendorName]);

        $sql = "SELECT id_Vendors, v_name, items.name FROM vendors JOIN items ON id_vendors = fid_vendor WHERE v_name = :vendorName";
        $sth = $dbh->prepare($sql);
        $sth->bindValue(":vendorName", $vendorName);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);

        echo "<table border='1'>";
        echo "<thead><tr><th>ID</th><th>Виробник</th><th>Товари</th></tr></thead><tbody>";
        foreach ($result as $item) {
            echo "<tr>";
            echo "<td>{$item['id_Vendors']}</td>";
            echo "<td>{$item['v_name']}</td>";
            echo "<td>{$item['name']}</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";

    } elseif ($categoryName) {
        $logQuery = $logDb->prepare("INSERT INTO log (query_type, param1) VALUES ('category', :param1)");
        $logQuery->execute([':param1' => $categoryName]);

        $sql = "SELECT id_category, c_name, items.name FROM category JOIN items ON id_category = fid_category WHERE c_name = :categoryName";
        $sth = $dbh->prepare($sql);
        $sth->bindValue(":categoryName", $categoryName);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);

        echo "<table border='1'>";
        echo "<thead><tr><th>ID</th><th>Категорія</th><th>Товари</th></tr></thead><tbody>";
        foreach ($result as $item) {
            echo "<tr>";
            echo "<td>{$item['id_category']}</td>";
            echo "<td>{$item['c_name']}</td>";
            echo "<td>{$item['name']}</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";

    } elseif ($pricerange) {
        list($minPrice, $maxPrice) = explode("-", $pricerange);
        $logQuery = $logDb->prepare("INSERT INTO log (query_type, param1, param2) VALUES ('price', :param1, :param2)");
        $logQuery->execute([':param1' => $minPrice, ':param2' => $maxPrice]);

        $sql = "SELECT id_items, name, price FROM items WHERE price BETWEEN :minPrice AND :maxPrice ORDER BY price ASC";
        $sth = $dbh->prepare($sql);
        $sth->bindValue(":minPrice", $minPrice);
        $sth->bindValue(":maxPrice", $maxPrice);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);

        echo "<table border='1'>";
        echo "<thead><tr><th>ID</th><th>Товар</th><th>Ціна</th></tr></thead><tbody>";
        foreach ($result as $item) {
            echo "<tr>";
            echo "<td>{$item['id_items']}</td>";
            echo "<td>{$item['name']}</td>";
            echo "<td>{$item['price']}</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
    }

    echo "<h3>Логи запитів:</h3>";
    $logResult = $logDb->query("SELECT * FROM log ORDER BY timestamp DESC");
    $logs = $logResult->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1'><tr><th>ID</th><th>Тип</th><th>Параметр 1</th><th>Параметр 2</th><th>Час</th></tr>";
    foreach ($logs as $log) {
        $time = (new DateTime($log['timestamp'], new DateTimeZone('UTC')))
                    ->setTimezone(new DateTimeZone('Europe/Kiev'))
                    ->format('Y-m-d H:i:s');

        echo "<tr>
                <td>{$log['id']}</td>
                <td>{$log['query_type']}</td>
                <td>{$log['param1']}</td>
                <td>" . ($log['param2'] ?? '') . "</td>
                <td>{$time}</td>
              </tr>";
    }
    echo "</table>";

} catch (PDOException $ex) {
    echo $ex->getMessage();
}
?>
