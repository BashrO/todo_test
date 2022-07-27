<?php
class Todo{
    protected $DB;
    // variables in DB, (more notes at the end of file);
    protected $vars=[
        'id'=>[
            'type'=>'varchar',
            'length'=>32,
            'isPrime'=>true
        ],
        'title'=>[
            'type'=>'varchar',
            'length'=>255,
            'default'=>'NULL',
        ],
        'txt'=>[
            'type'=>'varchar',
            'length'=>255,
            'default'=>'NULL',
        ],
        'status'=>[
            'type'=>'tinyint',
            'length'=>1,
            'default'=>0,
        ],
        'trash'=>[
            'type'=>'tinyint',
            'length'=>1,
            'default'=>0,
        ]
    ];
    //  DB assigned when object constructed
    public function __construct($DB){
        $this->DB = $DB;
    }

    // checks db/table exists and creates them if nedded, return list of exists tasks and trashed tasked OR empty Arrays
    public function initiate(){
        $gotBoth=0;
        $gotDB=$this->DB->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'todo'");
        if($gotDB->numRows()>0){
            $gotBoth++;
            $gotTBL = $this->DB->query("SHOW TABLES LIKE 'todo' ");
            if($gotTBL->numRows()>0){
                $gotBoth++;
            }
        }
        // if db or table missing, create it.
        if($gotBoth<2){
            $this->DB->initiateDB('todo',$this->vars);
            return [
                'todo'=>[],
                'trash'=>[],
                'txt'=>'created'
            ];
        }
        else{
            return [
                'todo'=>$this->fetchToDo(0),
                'trash'=>$this->fetchToDo(1),
                'txt'=>'fetched'
            ];
        }
    }

    // add new task
    public function add($data){
        $data=json_decode($data,true);
        $q='INSERT INTO todo (id,title,txt) VALUES (?,?,?)';
        $res=$this->DB->query($q,$data['id'],$data['title'],$data['txt']);
        return [
            'res'=>$res?'OK':'ERROR',
            'note'=>$res?'new task added':'failed saving task',
        ];
    }

    // update task (can change task from list to trash)
    public function update($data){
        $data=json_decode($data,true);
        $set='';
        foreach($data as $i=>$v){
            if($i=='id'){continue;}
            $set.=($set!=''?',':'').$i.'='.(is_numeric($v)?$v:'"'.$this->DB->connection->real_escape_string($v).'" ').' ';
        }
        $q='UPDATE todo SET '.$set.' WHERE id='.$data['id'];
        $res=$this->DB->query($q)->affectedRows();
        return [
            'res'=>$res?'OK':'ERROR',
            'note'=>$res?'task updated':'failed updating task',
        ];
    }

    // permanent delete
    public function delete($id){
        $q='DELETE FROM todo WHERE id=?';
        $res=$this->DB->query($q,$id)->affectedRows();
        return [
            'res'=>$res?'OK':'ERROR',
            'note'=>$res?'task deleted':'failed deleting task',
        ];
    }
    
    // fetch, used in $this->initiate funtion
    private function fetchToDo($trash){
        return $this->DB->query('SELECT * FROM todo WHERE trash='.$trash.' ORDER BY id DESC')->fetchAll();
    }

}

/*
///  DB.class.php => didn't wrote it now, used somthing I had



regrading "$vars" I usualy creates one object that contains all rules and data for both FE and BE.
    ie:

    'active'=>[
        'type'=>'select',
        'options'=>[
        '0'=>'מוקפא',
        '1'=>'פעיל',
        ],
        'label'=>'פעילה',
        'db'=>[
            'dbType'=>'tinint',
            'length'=>1,
            'default'=>1,
        ],             
        'toShow'=>true,
        'toEdit'=>true,
        'inNew'=>true,
        'inputExtra'=>[
            'step'=>1,
            'min'=>0,
            'max'=>1,
        ]
    ],

*/