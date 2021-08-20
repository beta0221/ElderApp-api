<template>
    <div id="flash-alert">
        <v-alert
            v-for="msg in msgStack"
            v-bind:key="msg.msg"
            value="true"
            :type="msg.type"
        >
            {{msg.msg}}
        </v-alert>
    </div>
</template>

<script>
export default {
    data(){
        return{
            msgStack:[]
        }
    },
    mounted() {
        EventBus.$on("flashAlert",(msg,type='success') =>{
            this.alertMsg(msg,type);
        });
    },
    destroyed() {
        EventBus.$off("flashAlert");
    },
    methods:{
        alertMsg(msg,type){
            this.msgStack.push({msg,type});
            setTimeout(()=>{
                this.msgStack.splice(0,1);
            },2000);
        }
    }
}
</script>

<style>
#flash-alert{
    position: fixed;
    width: 280px;
    top: 20px;
    right: 20px;
    z-index: 100;
}
</style>