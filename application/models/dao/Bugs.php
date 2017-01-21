<?php
require_once("entities/Bug.php");

class Bugs  {
    /**
     * Saves bug to database.
     * 
     * @param Bug $bug
     */
    public function save(Bug $bug) {
        DB::execute("
            INSERT INTO bugs (environment, type, file, line, message, trace, date, counter)
            VALUES (:environment, :type, :file, :line, :message, :trace, :date, 1)
            ON DUPLICATE KEY UPDATE counter = counter+1, date=:date
            ",array(
                ":environment"=>serialize($bug->environment),
                ":type"=>get_class($bug->exception),
                ":file"=>$bug->exception->getFile(),
                ":line"=>$bug->exception->getLine(),
                ":message"=>substr($bug->exception->getMessage(),0,254),
                ":trace"=>$bug->exception->getTraceAsString(),
                ":date"=>date("Y-m-d H:i:s"))
            );
    }
    
    /**
     * Gets bug full details.
     * 
     * @param integer $id
     * @return Bug
     */
    public function getDetails($id) {
        $info = DB::execute("SELECT * FROM bugs WHERE id=:id",array(":id"=>$id))->toRow();
        $bug = new Bug();
        $bug->date = $info["date"];
        $ei = new ExceptionInformation();
        $ei->file = $info["file"];
        $ei->type = $info["type"];
        $ei->line = $info["line"];
        $ei->message = $info["message"];
        $ei->trace = $info["trace"];
        $bug->exception = $ei;
        $bug->environment = unserialize($info["environment"]);
        $bug->id = $info["id"];
        $bug->count = $info["counter"];
        return $bug;
    }
    
    /**
     * Gets all bugs from database.
     * 
     * @return array List of bugs with afferent info.
     */
    public function getAll($limit, $offset) {
        $results = array();
        $results["data"] = DB::execute("SELECT SQL_CALC_FOUND_ROWS id, type,file,line,message,trace,counter,date FROM bugs ORDER BY date DESC LIMIT $limit OFFSET $offset")->toList();
        $results["total"] = DB::execute("SELECT FOUND_ROWS()")->toValue();
        return $results;
    }
    
    /**
     * Deletes bug from database.
     * 
     * @param integer $id
     */
    public function delete($id) {
        DB::execute("DELETE FROM bugs WHERE id=:id",array(":id"=>$id));
    }
}