<?php
/**
* 功能：数据库类（mysqli版）
* 作者：杨
* 时间：2017/1/29
* 修改：2017/3/16:增加status变量作为上一个SQL语句的执行状态
*       2017/3/18:修改debug报错的输出条件，去掉status增加code为上一句SQL语句执行返回的code，0为执行成功;
*       2017/3/19:修改exit();
* 注：看了一下mysqli和mysql，显然mysqli高级一点，mysqli有面向对象和面向过程两种写法
*     因为如果用面向对象，在类内部的函数里面要么一直用$this->mysqli,要么在前面赋值一
*     次，有点麻烦，所以就用函数比较好。
*/
include_once 'dbConfig.php';

class DataBase {

     public  $debug;                        //调试开启
     public  $results;                      //数据库查询结果集（数组）
     public  $code;                         //上一个SQL语句执行返回的code
     public  $debugContent;                 //报错内容
     private $db_host;                      //数据库主机
     private $db_user;                      //数据库登陆名
     private $db_pwd;                       //数据库登陆密码
     private $db_charset;                   //数据库字符编码
     private $db_name;                      //数据库名
     private $mysqli;                       //数据库连接标识
     private $msg = "";                     //数据库操纵信息

     /*new对象时可以什么都不填,一般填一个数据库名字*/
     public function __construct($db_name = '', $debug = false, $db_host = dbHost, $db_user = dbUser, $db_pwd = dbPassword, $db_charset = dbCharset) {
         $this->db_host = $db_host;
         $this->db_user = $db_user;
         $this->db_pwd  = $db_pwd;
         $this->db_name = $db_name;
         $this->db_charset = $db_charset;
         $this->debug   = $debug;
         $this->results = array();
         $this->initConnect();
     }

     /*连接数据库*/
     public function initConnect() {
         $mysqli = new mysqli($this->db_host, $this->db_user, $this->db_pwd, $this->db_name);
         //连接失败时的返回
         if ($mysqli->connect_errno) {
            $debugContent  = "错误原因：: Unable to connect to MySQL." . "<br/>";
            $debugContent .= "错误代码: " . $mysqli->connect_errno . "<br/>";   //这里用的是面向过程的函数
            $debugContent .= "错误解释: " . $mysqli->connect_error . "<br/>";   //这里用的是面向过程的函数
            if ($this->debug) {
                echo $debugContent;
            }
         }else if($this->debug){
            echo "连接数据库".$this->db_name."成功（初始连接）。<br/>";
         }
         //设置编码为utf8
         if (!$mysqli->set_charset("utf8")) {
            $debugContent  = "设置编码格式时发送错误: " . $mysqli->error . "<br/>";
            $debugContent .= "错误代码: " . $mysqli->errno . "<br/>";
            if ($this->debug) {
                echo $debugContent;
            }
         }
         $this->mysqli = $mysqli;
     }

     /*选择数据库*/
     public function selectDb($dbname) {
         $this->db_name = $dbname;
         if (mysqli_select_db($this->mysqli, $dbname)) {
             $debugContent  = "连接数据库时发生错误: " . mysqli_error($this->mysqli) . "<br/>";
             $debugContent .= "错误代码: " . mysqli_errno($this->mysqli) . "<br/>";
             if ($this->debug) {
                echo $debugContent;
             }
         }else if($this->debug){
            echo "连接数据库".$dbname."成功（后继连接）。<br/>";
         }
     }

     /*执行SQL语句*/
     public function query($sql) {
         if (!mysqli_real_query($this->mysqli, $sql)) {
             $debugContent  = "SQL语句执行时出错: " . mysqli_error($this->mysqli) . "<br/>";
             $debugContent .= "错误代码: " . mysqli_errno($this->mysqli) . "<br/>";
             $debugContent .= "SQL语句: " . $sql . "<br/>";
             if ($this->debug) {
                echo $debugContent;
             }
         }else if($this->debug){
            echo "SQL语句执行成功：".$sql."<br/>";
         }
         $this->code = mysqli_errno($this->mysqli);
         if ($result = mysqli_store_result($this->mysqli)) {
            while($row = mysqli_fetch_array($result)){
                $this->results[] =  $row;
            } 
            // $this->results = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_free_result($result);
         }
         if ($this->debug) {
             if($this->results){
                 echo "受影响行数：" . mysqli_affected_rows($this->mysqli) . "<br/>";
                 if ($this->debug === "print") {
                    print_r($this->results);
                 }
                 echo "<br/>";
             }
         }
         return $this->results;
     }
 }