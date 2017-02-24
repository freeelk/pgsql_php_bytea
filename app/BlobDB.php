<?php
/**
 * Created by PhpStorm.
 * User: freeelk
 * Date: 23.02.17
 * Time: 16:54
 */

namespace PostgreSQLTutorial;


class BlobDB
{

    public $pdo;


    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Insert a file into the company_files table
     * @param int $stockId
     * @param string $fileName
     * @param string $mimeType
     * @param string $pathToFile
     * @return int
     * @throws \Exception
     */
    public function insert($stockId, $fileName, $mimeType, $pathToFile) {
        if (!file_exists($pathToFile)) {
            throw new \Exception("File %s not found.");
        }

        $sql = "INSERT INTO company_files(stock_id,mime_type,file_name,file_data) "
            . "VALUES(:stock_id,:mime_type,:file_name,:file_data)";

        try {
            $this->pdo->beginTransaction();

            // create large object
            $fileData = $this->pdo->pgsqlLOBCreate();
            $stream = $this->pdo->pgsqlLOBOpen($fileData, 'w');

            // read data from the file and copy the the stream
            $fh = fopen($pathToFile, 'rb');
            stream_copy_to_stream($fh, $stream);
            //
            $fh = null;
            $stream = null;

            $stmt = $this->pdo->prepare($sql);

            $stmt->execute([
                ':stock_id' => $stockId,
                ':mime_type' => $mimeType,
                ':file_name' => $fileName,
                ':file_data' => $fileData,
            ]);

            // commit the transaction
            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }

        return $this->pdo->lastInsertId('company_files_id_seq');
    }

    /**
     * Read BLOB from the database and output to the web browser
     * @param int $id
     */
    public function read($id) {

        $this->pdo->beginTransaction();

        $stmt = $this->pdo->prepare("SELECT id, file_name, file_data, mime_type, octet_length(file_data) as file_length "
            . "FROM company_files "
            . "WHERE id= :id");

        // query blob from the database
        $stmt->execute([$id]);

        $stmt->bindColumn('file_data', $fileData, \PDO::PARAM_STR);
        $stmt->bindColumn('mime_type', $mimeType, \PDO::PARAM_STR);
        $stmt->bindColumn('file_name', $fileName, \PDO::PARAM_STR);
        $stmt->bindColumn('file_length', $fileLength, \PDO::PARAM_STR);
        $stmt->fetch(\PDO::FETCH_BOUND);
        $stream = $this->pdo->pgsqlLOBOpen($fileData, 'r');

        echo $fileLength;
        exit;
        $this->fileForceDownload($fileName, $mimeType, $stream);
    }

    /**
     * Delete the large object in the database
     * @param int $id
     * @throws \Exception
     */
    public function delete($id) {
        try {
            $this->pdo->beginTransaction();
            // select the file data from the database
            $stmt = $this->pdo->prepare('SELECT file_data '
                . 'FROM company_files '
                . 'WHERE id=:id');

            $stmt->bindColumn('file_data', $fileData, \PDO::PARAM_STR);
            $stmt->execute(['id'=>$id]);
            $stmt->fetch();


            $stmt->closeCursor($id);
            echo 'id: ' . $id;
            echo 'file data: ' . $fileData;
            //delete the large object
            $this->pdo->pgsqlLOBUnlink($fileData);
            $stmt = $this->pdo->prepare("DELETE FROM company_files WHERE id = :id");
            $stmt->execute([$id]);

            $this->pdo->commit();

        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function fileForceDownload($fileName, $mimeType, $stream) {

            // сбрасываем буфер вывода PHP, чтобы избежать переполнения памяти выделенной под скрипт
            // если этого не сделать файл будет читаться в память полностью!
            if (ob_get_level()) {
                ob_end_clean();
            }
            // заставляем браузер показать окно сохранения файла
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . $fileName);
            header('Content-Transfer-Encoding: binary');
            header("Content-type: " . $mimeType);
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');

            // читаем и отправляем его пользователю
            fpassthru($stream);
            exit;
    }

}