<template>
    <div>
        <v-dialog v-model="show" max-width="480px">
            <v-card>

                <v-card-title class="headline">
                    訊息
                </v-card-title>

                <div style="padding:8px 12px;height:240px">
                    <textarea v-html="content"></textarea>
                </div>
                    
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn @click="show = false">關閉</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
export default {
    mounted() {
        EventBus.$on("showTextAreaModal",content =>{
            this.content = '';
            this.show = true;
            this.content = content;
        });
    },
    destroyed() {
        EventBus.$off("showTextAreaModal");
    },
    data(){
        return{
            show:false,
            content:''
        }
    },
}
</script>

<style>
textarea{
    width: 100%;
    border: 1px solid lightgray;
    border-radius: 0.2rem;
    height: 100px;
    padding: 4px 8px;
    height: 100%;
}
</style>