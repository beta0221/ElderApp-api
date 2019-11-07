<template>
  <v-dialog v-model="group_tree_dialog" max-width="1000px">
        <v-card class="member-tree">

          <div>

            
            <div id="left-panel">
                <iframe id="tree-frame" class="border fill" v-if="group_tree_dialog" :src="tree_src" frameborder="0"/>
            </div>


            <div id="right-panel">
                <div class="border fill" style="padding:8px">
                    <input class="search-bar" type="text" placeholder="搜尋" v-model.lazy="search_text" @change="searchUser"/>
                    <div class="devider"></div>
                    <div class="border search-box">
                        <div class="member-cell" 
                        @click="selectUser(user.id)"
                        v-for="user in search_result" 
                        v-bind:key="user.id">
                        {{user.name}}
                        </div>
                    </div>
                    <div class="devider"></div>
                    <div class="border level-box">
                        <div class="level-cell border" v-for="l in level_array" v-bind:key="l.level">{{l.name}}</div>
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
        EventBus.$on("showMemberTree",id_code=>{
            this.group_tree_dialog = true;
            this.tree_src = '/member_tree/'+id_code;
        })
    },
    data(){
        return{
            group_tree_dialog: false,
            tree_src:'',
            search_text:'',
            search_result:[],
            select_user:{},
            level_array:[
                {'level':5,'name':'長老天使'},
                {'level':4,'name':'領航天使'},
                {'level':3,'name':'守護天使'},
                {'level':2,'name':'大天使'},
                {'level':1,'name':'小天使'},
            ]
        }
    },
    methods:{
        selectUser(user_id){
            this.select_user = user_id;
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
    width:calc(60% - 2px);
    height: 600px;
    display: inline-block;
    padding: 8px;
}
#right-panel{
    display: inline-block;
    vertical-align: top;
    height: 600px;
    width:calc(40% - 2px);
    padding: 8px;
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
    background-color: #fff;
    line-height: 30px;
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
    cursor: pointer;
}
</style>