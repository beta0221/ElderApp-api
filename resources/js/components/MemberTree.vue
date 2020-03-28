<template>
  <v-dialog v-model="group_tree_dialog" max-width="90%">
        <v-card class="member-tree">

          <div>

              <div id="left-panel">
                <div class="border fill left-container" style="padding:8px">
                    <div class="left-container-item">
                        <div style="width:100%;height:100%;font-size:24px;">
                            指派職務為
                        </div>
                    </div>
                    <div class="left-container-item"
                        v-for="l in org_rank_array" 
                        v-bind:key="l.level">
                        <v-btn @click="makeGroupLeader(l.level,l.name)">
                            {{l.name}}
                        </v-btn>
                    </div>
                    <div class="left-container-item">
                        <div style="width:100%;height:100%;font-size:24px;">
                            成為老師
                        </div>
                    </div>
                    <div class="left-container-item">
                        <v-btn @click="makeTeacher()">成為老師</v-btn>
                    </div>
                </div>
              </div>
            
            <div id="middle-panel">
                <iframe id="tree-frame" class="border fill" v-if="group_tree_dialog" :src="tree_src" frameborder="0"/>
            </div>


            <div id="right-panel">
                <div class="border fill" style="padding:8px">
                    <input class="search-bar" type="text" placeholder="搜尋" v-model.lazy="search_text" @change="searchUser"/>
                    <div class="devider"></div>
                    <div class="border search-box">
                        <div class="member-cell"
                        :class="(select_user==user.id)?'select-cell':''"
                        @click="selectUser(user.id)"
                        v-for="user in search_result" 
                        v-bind:key="user.id">
                        {{user.name}}
                        </div>
                    </div>
                    <div class="devider"></div>
                    <div class="border level-box">
                        <div class="level-cell border"
                        :class="(l.level < select_user_level)?'green-cell':''"
                        v-for="l in level_array" 
                        v-bind:key="l.level"
                        @click="joinByLevel(l.level)">
                        {{l.name}}
                        </div>
                    </div>

                </div>
            </div>

          </div>

          
          
        </v-card>
    </v-dialog>
</template>

<script>
export default {
    created(){
        EventBus.$on("showMemberTree",user=>{
            this.group_tree_dialog = true;
            this.tree_src = '/member_tree/'+user.id_code;
            this.current_user_id = user.id;
        })
    },
    data(){
        return{
            current_user_id:'',
            select_user:'',
            select_user_level:0,
            group_tree_dialog: false,
            tree_src:'',
            search_text:'',
            search_result:[],
            level_array:[
                {'level':5,'name':'領航天使'},
                {'level':4,'name':'守護天使'},
                {'level':3,'name':'大天使'},
                {'level':2,'name':'小天使'},
                {'level':1,'name':'平民'},
            ],
            org_rank_array:[
                {'level':5,'name':'領航天使'},
                {'level':4,'name':'守護天使'},
                {'level':3,'name':'大天使'},
                {'level':2,'name':'小天使'},
            ],
        }
    },
    methods:{
        selectUser(user_id){
            this.select_user = user_id;
            axios.get(`/api/getUserLevel/${user_id}`)
            .then(res=>{
                this.select_user_level = res.data;
            })
            .catch(error=>{
                console.log(error);
            })
        },
        searchUser(){
            axios.get('/api/search-member', {
                params: {
                    searchColumn:'name',
                    searchText:this.search_text,
                }
            })
            .then(res=>{
                this.search_result = res.data;
            })
            .catch(error =>{
                console.log(error);
            })
        },
        joinByLevel(level){
            axios.post('/api/addGroupMember', {
                'leader_id':this.select_user,
                'user_id':this.current_user_id,
                'level':level,
            })
            .then(res=>{
                alert(res.data.m);
                if(res.data.s == 1){
                    document.getElementById('tree-frame').contentWindow.location.reload(true);
                }
            })
            .catch(error=>{
                console.log(error);
            })
        },
        makeGroupLeader(level,name){
            if(!confirm('確定指派為'+name)){
                return;
            }
            axios.post('/api/makeGroupLeader', {
                'user_id':this.current_user_id,
                'level':level,
            })
            .catch(error=>{console.log(error);})
            .then(res=>{
                alert(res.data.m);
                if(res.data.s == 1){
                    document.getElementById('tree-frame').contentWindow.location.reload(true);
                }
            })
        },
        makeTeacher(){
            if(!confirm('確定指派為老師')){
                return;
            }
            axios.post('/api/makeTeacher', {
                'user_id':this.current_user_id,
            })
            .catch(error=>{console.log(error);})
            .then(res=>{
                alert(res.data.m);
            })
        }
    }
}
</script>

<style>
.border{
    border:1px solid gray;
    border-radius: .3rem;
}
.fill{
    width: 100%;
    height: 100%;
}
#left-panel{
    width:calc(20% - 3px);
    vertical-align: top;
    height: 600px;
    display: inline-block;
    padding: 8px 4px;
}
#middle-panel{
    width:calc(60% - 3px);
    vertical-align: top;
    height: 600px;
    display: inline-block;
    padding: 8px 4px;
}
#right-panel{
    display: inline-block;
    vertical-align: top;
    height: 600px;
    width:calc(20% - 3px);
    padding: 8px 4px;
}
.left-container{
    display: flex;
    flex-direction: column;
    flex-wrap: nowrap;
    align-items: stretch;
}
.left-container-item{
    flex: 1;
    text-align: center;
}
.member-tree input{
    width: 100%;
    border: 1px solid lightgray;
    border-radius: 0.2rem;
    height: 32px;
    padding: 0 12px;
    background-color: #fff;
}
.search-bar{
    height: 24px;
}
.devider{
    height: 8px;
}
.search-box{
    height: calc(50% - 32px);
    overflow-y: scroll;
}
.search-box .member-cell{
    width: 100%;
    border: 1px solid lightgray;
    border-radius: 0.2rem;
    height: 32px;
    padding: 0 12px;
    line-height: 30px;
    cursor: pointer;
}
.member-cell:hover{
    background-color: lightgray;
    color:#fff;
}
.select-cell{
    background-color: gray!important;
    color:#fff;
}
.level-box{
    height: calc(50% - 16px);
    padding: 8px;
    display: flex;
    flex-direction: column;
    justify-content: space-around;
}
.level-cell{
    height: 32px;
    padding: 0 12px;
    line-height: 30px;
}
.green-cell{
    background-color: #5cb85c;
    border-color:#5cb85c;
    color:#fff;
    cursor: pointer;
}
.green-cell:hover{
    background-color:#449d44;
    border-color: #449d44;
}
</style>