<?php
require_once 'env.php';
class ConnectDatabase
{
    //    Các thuộc tính tạo sẵn theo base
    public $pdo;
    public $sql;
    public $sta;
    public function __construct()
    {
        // Xử lý ngoại lệ
        try {
            $this->pdo = new PDO(
                "mysql:host=" . DBHOST . ";dbname=" . DBNAME .
                    "; charset=" . DBCHARSET,
                DBUSER,
                DBPASS
            );
            // cai dat xu ly ngoai le
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            //            echo "ket noi thanh cong";
        } catch (PDOException $e) {
            echo "Ket noi that bai" . $e->getMessage();
        }
    }
    //   Các phương thức tạo sẵn theo base
    public function setQuery($sql)
    {
        $this->sql = $sql;
    }
    // hàm này sẽ làm hàm chạy câu truy vấn
    public function execute($options = array())
    {
        try {
            // Chuẩn bị câu truy vấn SQL
            $this->sta = $this->pdo->prepare($this->sql);

            // Nếu có options (các giá trị để bind)
            if ($options) {
                // Lặp qua từng option
                for ($i = 0; $i < count($options); $i++) {
                    // Bind tham số ở index $i+1 với giá trị tương ứng trong mảng $options
                    $this->sta->bindParam($i + 1, $options[$i]);
                }
            }

            // Thực thi câu truy vấn đã chuẩn bị
            $this->sta->execute();

            // Trả về câu truy vấn đã chuẩn bị
            return $this->sta;
        } catch (PDOException $e) {
            // Xử lý ngoại lệ PDOException
            // Ở đây có thể log lỗi, thông báo cho người dùng hoặc thực hiện các hành động khác
            // Ví dụ:
            echo "Lỗi khi thực thi truy vấn: " . $e->getMessage();
            // hoặc
            // throw $e; // Chuyển ngoại lệ để xử lý ở nơi khác nếu cần
        }
    }
    //    Truy vấn
    public function loadData($options = array(), $getAllData = true)
    {
        try {
            // Nếu không có options được cung cấp
            if (!$options) {
                // Thực thi truy vấn mặc định
                if (!$result = $this->execute())
                    return false;
            } else {
                // Nếu có options, thực thi truy vấn với các options đã cho
                if (!$result = $this->execute($options))
                    return false;
            }
            if ($getAllData == true) {
                // Trả về tất cả các hàng từ kết quả truy vấn dưới dạng một mảng các đối tượng
                return $result->fetchAll(PDO::FETCH_OBJ);
            } else {
                return $result->fetch(PDO::FETCH_OBJ);
            }
        } catch (PDOException $e) {
            // Xử lý ngoại lệ PDOException
            // Có thể log lỗi, thông báo cho người dùng hoặc thực hiện các hành động khác
            // Ví dụ:
            echo "Lỗi truy vấn: " . $e->getMessage();
            // hoặc
            // throw $e; // Chuyển ngoại lệ để xử lý ở nơi khác nếu cần
        }
    }
}
