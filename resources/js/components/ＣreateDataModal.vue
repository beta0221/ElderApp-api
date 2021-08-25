<template>
<div>
    <v-dialog  v-model="show" max-width="520px">

        <v-card>
            <v-card-title class="headline">
                新增
            </v-card-title>

            <div id="create-data-modal" style="padding:12px">
                

                <div v-for="column in columns" v-bind:key="column.name">
                    <h5>{{column.text}}</h5>

                    <input v-if="column.type == 'text'" type="text" :name="column.name" v-model="data[column.name]">


                </div>

            </div>



            <v-card-actions>
                
                <v-spacer></v-spacer>
                <v-btn @click="show = false">關閉</v-btn>
                <v-btn @click="createRequest" color="success">新增</v-btn>
            </v-card-actions>


        </v-card>

    </v-dialog>
</div>
</template>

<script>
export default {
    props:['createUrl','columns'],
    data(){
        return{
            data:{},
            show:false,
        }
    },
    mounted(){
        EventBus.$on('showCreateDatalModal',(_) => {
            this.show = true;
            this.data = {};
        });
    },
    destroyed(){
        EventBus.$off('showCreateDatalModal');
    },
    methods:{
        createRequest(){
            axios.post(this.createUrl,this.data)
            .then(res => {
                this.data = {};
                EventBus.$emit('reloadData');
            })
            .catch(err => {
                Exception.handle(err);
            })
        }
    }
}
</script>

<style>
#create-data-modal input,#create-data-modal select,#create-data-modal textarea{
    padding: 4px 8px;
    border:1px solid lightgray;
    border-radius: .2rem;
    display: block;
    width: 100%;
}
</style>