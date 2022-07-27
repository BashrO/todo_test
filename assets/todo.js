'use strict';
const toDo = {
    fromDB: false,
    listType:{
        todo:{
            elemID:'toDoList',
            localDB:'todoList',
            title:'Tasks To Do',
            trash:false,
        },
        trash:{
            elemID:'trashList',
            localDB:'trashList',
            title:'Trash',            
            trash:false,
        },
    },
    // a short version of fetching function I'm used to using, (replaced my old ajax function)' 
    async postData(url = 'api/proce.php', data = {},method = 'POST') {
        const response = await fetch(url, {
        method, 
        headers: {"Content-type": "application/x-www-form-urlencoded; charset=UTF-8"},
        body: Object.entries(data).map(([k,v])=>{return k+'='+v}).join('&')
        });
        return response.json();
    },

    // returns list from localstorage or empty array if not exists
    getList(type){
        let list=localStorage.getItem(this.listType[type].localDB);
        return list!==null?JSON.parse(list):[];
    },
    
    // updates localstorage
    updateList(newList,type='todo'){
        localStorage.setItem(this.listType[type].localDB, JSON.stringify(newList))
    },

    //add func. uses both to add new, and construct basic object when updating exists task
    async add(form,toUpdate=false) {
        let title = document.querySelector(`#${form.id} #title`);
        let txt = document.querySelector(`#${form.id} #txt`);
        // min length for title and description
        if (title.value.trim().length < 3) {
            alert('title too short')
            return false;
        } 
        else if (txt.value.trim().length < 3) {
            alert('text too short')
            return false;
        } 
        // if update exists task, return object
        else if (toUpdate){
            return {
                title: title.value,
                txt: txt.value
            };
        }
        else {
            let newInList={
                id: Date.now(),
                title: title.value,
                txt: txt.value,
                status:0
            };
            // the "dbUpdated" confirms db update in case app running on Database mode, this check exists in all update/delete/add function
            let dbUpdated=this.fromDB? await this.postData('api/proce.php',{doWhat:'add',newInList:JSON.stringify(newInList)}).then(d=>d):{res:'OK'};
            if(dbUpdated.res!='OK'){alert('update faild'); return false;}
            let newList=toDo.getList('todo');
            newList.push(newInList);
            this.updateList(newList);
            let newBlock=this.renderBlock(newInList);
            document.getElementById('toDoList').appendChild(newBlock)
            title.value = '';
            txt.value = '';
            return true;
        }
    },

    // updates status state (done/undone) and change button displayed
    async statusUpdate(id,type){
        let elem=document.getElementById('done_'+id);
        let tmpList=this.getList(type);
        let i = tmpList.findIndex((obj => obj.id == id));
        let newStatus=tmpList[i].status==1?0:1;
        let dbUpdated=this.fromDB ? await this.postData('api/proce.php',{doWhat:'update',newInList:JSON.stringify({id,status:newStatus})}).then(d=>d):{res:'OK'};
        if(dbUpdated.res!='OK'){alert('update faild'); return false;}
        tmpList[i].status =newStatus;
        this.updateList(tmpList,type);
        newStatus==1?elem.classList.add('done'):elem.classList.remove('done');
        elem.innerText =newStatus==1?'Done':'Undone';
    },

    // updates title/description
    async update(form,type='todo') {
        let newData=await this.add(form,true);
            if(typeof newData!="undefined" && !newData){return false;}
        let id=form.dataset.elemId;
        let tmpList=this.getList(type);
        let i = tmpList.findIndex((obj => obj.id == id));
        tmpList[i].title =newData.title
        tmpList[i].txt =newData.txt
        let dbUpdated=this.fromDB? await this.postData('api/proce.php',{doWhat:'update',newInList:JSON.stringify(tmpList[i])}).then(d=>d):{res:'OK'};
        if(dbUpdated.res!='OK'){alert('update faild'); return false;}
        let newBlock=this.renderBlock(tmpList[i],type);
        let oldBlock=document.getElementById('inList_'+id)
        oldBlock.parentNode.replaceChild(newBlock, oldBlock);
    },
    //del function used for task list and trash list deleting AND for restoring from trash to todo list, 
    // in case task deleted: FE->move from todo list to trash list BE->updates trash to 1;
    // in case trash deleted: FE->deleted from trash list BE->deleted from DB;
    // in case trash restored: FE->move from trash list to todo list BE->updates trash to 0;

    async del(id,type='todo',restore=false) {
        let tmpList=this.getList(type);
        let i = tmpList.findIndex((obj => obj.id == id));
        let inTrash=tmpList[i];
        tmpList.splice(i, 1);
        this.updateList(tmpList,type);
        document.getElementById('inList_'+id).remove();
        if(type=='todo' || type=='trash' && restore){
            let dbUpdated=this.fromDB ? await this.postData('api/proce.php',{doWhat:'update',newInList:JSON.stringify({id,trash:type=='todo'?1:0})}).then(d=>d):{res:'OK'};
            if(dbUpdated.res!='OK'){alert('update faild'); return false;}
            let trashList=this.getList(type=='todo'?'trash':'todo');
            trashList.push(inTrash);
            this.updateList(trashList,type=='todo'?'trash':'todo');        
            this.renderList(type=='todo'?'trash':'todo');
        }
        else{
            let dbUpdated=this.fromDB ? await this.postData('api/proce.php',{doWhat:'delete',id}).then(d=>d):{res:'OK'};
            if(dbUpdated.res!='OK'){alert('update faild'); return false;}
        }
    },

    // creates update form and append it to tasks' parrent div;
    addUpdateForm(id,type){
        let updateBlock=document.createElement('form');
        let formID='toDoUpdate_'+id;
        updateBlock.id=formID;
        updateBlock.dataset.function="update";
        updateBlock.dataset.elemId=id;
        let tmpList=this.getList(type);
        let i = tmpList.findIndex((obj => obj.id == id));
        updateBlock.innerHTML=`<div>Update</div>
        <div><input type="text" id="title" placeholder="Enter title" value="${tmpList[i].title}"></div>
        <div><textarea id="txt" placeholder="Enter description">${tmpList[i].txt}</textarea></div>
        <div><button class="btn update"type="submit">UPDATE</button></div>`;
        document.getElementById('inList_'+id).appendChild(updateBlock);
        updateBlock.addEventListener('submit', (e) => {
            e.preventDefault();
            toDo[e.target.dataset.function](e.target)
        })
    },

    // rendering task block
    renderBlock(obj,type='todo'){
        let block=document.createElement('div');
        block.id='inList_'+obj.id;
        block.classList.add('inListBlock');
        block.dataset.type=type;
        block.innerHTML=`
            <div class="title">${obj.title}</div>
            <div class="txt">${obj.txt}</div>
            <div class="action">
            ${type=='trash'?
            `<button class="btn restore"  onClick="toDo.del(${obj.id},'${type}',true)">Restore</button>`:
            `<button class="btn edit"  onClick="toDo.addUpdateForm(${obj.id},'${type}')">Edit</button>`
            }
            <button class="btn delete" onClick="toDo.del(${obj.id},'${type}')">Delete</button>
            <button class="btn status ${obj.status==1?'done':''}" data-status="${obj.status}" id="done_${obj.id}" data-id="${obj.id}" onClick="toDo.statusUpdate('${obj.id}','${type}')">${obj.status==1?'Done':'Undone'}</button>
            </div>
        `;
        return block;
    },

    //render tasks list
    renderList(type){
        let curList=this.listType[type];
        let tmpList=this.getList(type);
        let listBLock=document.getElementById(curList.elemID)
        listBLock.innerHTML=`<h2>${curList.title}</h2>`;
        for (let i = 0; i < tmpList.length; i++) {
            listBLock.append(this.renderBlock(tmpList[i],type));
        }
    },
    
    // usualy I'm initiating all forms listiners, I did it out of habit;
    forms() {
        document.querySelectorAll('form').forEach(elem => {
            elem.addEventListener('submit', (e) => {
                e.preventDefault();
                toDo[e.target.dataset.function](e.target)
            })
        })
    },

    //inintiate display
    ini(withForms=false) {
        if(withForms){toDo.forms();}
        toDo.renderList('todo');
        toDo.renderList('trash');
    },

    // change source of data and re-render it using ini function 
    changeSource(elem){
        if(elem.value!=''){
            let fromDB=elem.value==1?true:false;
            this.fromDB=fromDB;
            if(this.fromDB){
                this.postData('api/proce.php',{doWhat:'ini'})
                .then((d) =>{
                    toDo.updateList(d.todo,'todo')
                    toDo.updateList(d.trash,'trash')
                })
                .then(()=>{
                    toDo.ini();
                })
            }
        }
    },
};

document.addEventListener("DOMContentLoaded", function() {
    toDo.ini(true);
})