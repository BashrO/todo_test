<?php
$curVersion='0.0.02';
// "To Do" by Roy Bashiry 
//          RoyBashiey@gmail.com
//          050-73-70-700        
// **************************************************************** 
//
// to initiate this test, edit connection in class/Db.class.php: default: user:'root', password:'',server:'localhost'
// all code written from scratch, only DB class 
//
// FILES:   
//     /
//      - index.php     ->  default page
//
//     assets/
//      - todo.js       ->  all js functions
//      - todo.css      ->  little bit of styling
//
//     todo/
//      - proce.php     -> NOT a classic rest api, I could have use four files and get/post/update..., to show my amazing 
//                         skills of API building. but one-stop shop for all 4 options, determined by $_POST['doWhat'] parameter, seemed more reasonable.
//                         
//     todo/class
//     - Db.class.php   -> database class, execpt initiate function, and minor changes to fit the test requirements, this class coppied from old code I had.
//     - todo.class.php -> main class create, read, update and delete todo tasks        
//
// **************************************************************** 
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>2Do list</title>
    <link type="text/css" rel="stylesheet" href="assets/todo.css?v=<?=$curVersion?>">
</head>
<body>
    <main>
        <div class="flex verse">
        <div class="flexCol">
                <h2>Mode change</h2>
                    <b>Current mode:</b> <select onchange="toDo.changeSource(this)">
                        <option value="0" selected>Local</option>
                        <option value="1">Database</option>
                    </select>
                    <ul>
                        <li>(!) Changing from "Local" to "Database" will delte all Local changes.</li>
                        <li>(i) All changes made in Database mode, will affect Local data.</li>
                        <li>(i) Deleting from Trash is permanent.</li>
                    </ul>
                </div>
        <div class="flexCol">
                    <form id="toDoAdd" data-function="add">
                        <h2>Add Task</h2>
                        <div><input type="text" id="title" placeholder="Enter title"></div>
                        <div><textarea id="txt" placeholder="Enter description"></textarea></div>
                        <div><input type="submit" class="btn save" value="SAVE"></div>
                    </form>
                </div>
        </div>
        <div class="flex">
            <div id="toDoList" class="flexCol"></div>
            <div id="trashList" class="flexCol"></div>
        </div>
    </main>
    <script src="assets/todo.js?v=<?=$curVersion?>"></script>
</body>
</html>