<?php
namespace leruge;

use think\Db;

class BackUp
{
    public function all($backFile)
    {
        $backDirectory = ROOT_PATH . 'runtime' . DS . 'backup';
        $realBackDirectory = $backDirectory . '/' . $backFile;
        if (!file_exists($backDirectory)) {
            $result = mkdir($backDirectory, 0777, true);
            if (!$result) {
                exception('runtime目录没有读写权限！', 1002);
            }
        }
        $dbName = 'Tables_in_' . config('database')['database'];
        if (!$dbName) {
            exception('数据库名称没有配置，请检查配置', 1001);
        }
        $tableName = Db::query('show tables');
        if (!$tableName) {
            exception('数据库中没有表，无法备份！', 1004);
        }
        $fp = fopen($realBackDirectory, 'a+') or exception('追加方式打开文件失败，请重新备份！', 1003);
        fwrite($fp, "/*\n *梦中PHP系列教程提供\n *QQ:305530751\n*/\n\n");
        foreach ($tableName as $k => $v) {
            $info = "DROP TABLE IF EXISTS `{$v[$dbName]}`;\n";
            $data = Db::query("show create table {$v[$dbName]}");
            $info .= $data[0]['Create Table'];
            fwrite($fp, $info . ";\n\n");

            $insertData = Db::name($v[$dbName])->select();
            foreach ($insertData as $k1 => $v1) {
                $insertSql = "INSERT INTO {$v[$dbName]} VALUES(";
                foreach ($v1 as $k2 => $v2) {
                    $insertSql .= "'" . $v2 . "',";
                }
                $insertSql = trim($insertSql, ',') . ');';
                fwrite($fp, $insertSql . "\n");
            }
        }
        fclose($fp);
        return ['code' => 1, 'message' => '备份完成！'];
    }
}