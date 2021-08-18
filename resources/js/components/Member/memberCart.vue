<template>
    <div>
        <v-dialog v-model="show" max-width="480px">
            <v-card>

                <v-card-title class="headline">
                    選取清單
                </v-card-title>

                <div style="padding:8px 12px">
                    
                    <div class="border member-cart-box">
                        <div
                            class="member-cell"
                            v-for="(user,index) in userList"
                            v-bind:key="user.id"
                            @click="clickUser(index,user.id)">
                            {{ user.name }} {{user.email}} <span :class="(user.valid==1)?'green--text':'red--text'">{{(user.valid==1)?'有效':'無效'}}</span>
                        </div>
                    </div>

                    <div>
                        <h3>名稱：</h3>
                        <textarea v-html="nameArray"></textarea>
                    </div>

                    <div>
                        <h3>ID：</h3>
                        <textarea v-html="idArray"></textarea>
                    </div>

                    

                </div>

                <v-card-actions>
                    
                    <v-spacer></v-spacer>
                    <v-btn @click="clear">清除</v-btn>
                    <v-btn @click="show = false">關閉</v-btn>
                    
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
export default {
    destroyed(){
        
    },
    mounted(){
        
    },
    data() {
        return {
            show:false,
            userList:[],
            userDict:{},
            nameArray:'',
            idArray:'',
        };
    },
    methods:{
        showModal(){
            this.show = true;
        },
        add(user){
            if(this.userDict[user.id]){ return; }
            this.userList.push(user);
            this.userDict[user.id] = true;
            this.refreshTextarea();
        },
        clickUser(index,id){
            delete this.userDict[id];
            this.userList.splice(index,1);
            this.refreshTextarea();
        },
        refreshTextarea(){
            let _nameArray = [];
            let _idArray = [];
            this.userList.forEach(user =>{
                _nameArray.push(user.name);
                _idArray.push(user.id);
            });
            this.nameArray = _nameArray.join();
            this.idArray = _idArray.join();
        },
        clear(){
            this.userList = [];
            this.userDict = {};
            this.nameArray = '';
            this.idArray = '';
        }
    }
}
</script>

<style>
.member-cart-box{
    height: 400px;
    overflow-y: scroll;
}
.member-cart-box .member-cell{
    width: 100%;
    border: 1px solid lightgray;
    border-radius: 0.2rem;
    height: 32px;
    padding: 0 12px;
    line-height: 30px;
    cursor: pointer;
}
textarea{
    width: 100%;
    border: 1px solid lightgray;
    border-radius: 0.2rem;
    height: 100px;
    padding: 4px 8px;
}
</style>