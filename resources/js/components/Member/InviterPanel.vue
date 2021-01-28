<template>
    <v-dialog v-model="dialog" max-width="480px">
        
        <v-card id="member-detail-dialog">
            <v-card-title class="headline">姓名：{{user.name}}</v-card-title>
            
            <div style="padding:8px">
                <UserSearchbox v-on:clickUser="selectUser"></UserSearchbox>
            </div>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="gray darken-1" flat="flat" @click="dialog = false">關閉</v-btn>
            </v-card-actions>

        </v-card>

    </v-dialog>
</template>

<script>
import UserSearchbox from '../UserSearchbox'

export default {
    components:{
        UserSearchbox,
    },
    created() {
        EventBus.$on("showInviterPanel", user => {
            this.dialog = true;
            this.user = user;
        });
    },
    destroyed(){
        EventBus.$off('showInviterPanel');
    },
    data() {
        return {
            user:{},
            dialog: false,
        };
    },
    methods:{
        selectUser(user_id){
            axios.post('/api/updateInviter', {
                user_id:this.user.id,
                inviter_id:user_id,
            })
            .then(res =>{
                this.dialog = false;
                EventBus.$emit("showMemberDetail",this.user);
            })
            .catch(error => {
                Exception.handle(error);
            })
            
        }
    }
}
</script>

<style>

</style>