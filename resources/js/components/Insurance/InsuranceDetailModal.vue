<template>
    <v-dialog  v-model="show" max-width="480px">
        <v-card>

            <v-card-title class="headline">
                申請表
            </v-card-title>

            <div id="insurance-detail-modal" style="padding:12px 24px">
                
                <div class="data-row">
                    <h4>申請人：</h4>
                    <span v-if="data.user != undefined">{{data.user.name}}</span>
                </div>
                
                <div class="data-row">
                    <h4>身分證：</h4>
                    <input type="text" v-model="data.identity_number">
                </div>

                <div class="data-row">
                    <h4>聯絡方式：</h4>
                    <input type="text" v-model="data.phone">
                </div>

                <div class="data-row">
                    <h4>生日：</h4>
                    <input type="text" v-model="data.birthdate">
                </div>

                <div class="data-row">
                    <h4>狀態：</h4>
                    
                </div>

                <div class="data-row">
                    <h4>職業：</h4>
                    <input type="text" v-model="data.occupation">
                </div>

                <div class="data-row">
                    <h4>關係：</h4>
                    <input type="text" v-model="data.relation">
                </div>

                <div class="data-row">
                    <h4>Q1：</h4>
                    <select v-model="data.q_1">
                        <option value="0">否</option>
                        <option value="1">是</option>
                    </select>
                </div>

                <div class="data-row">
                    <h4>Q2：</h4>
                    <select v-model="data.q_2">
                        <option value="0">否</option>
                        <option value="1">是</option>
                    </select>
                </div>

                <div class="data-row">
                    <h4>Q3：</h4>
                    <select v-model="data.q_3">
                        <option value="0">否</option>
                        <option value="1">是</option>
                    </select>
                </div>

                <div class="data-row">
                    <h4>Q4：</h4>
                    <select v-model="data.q_4">
                        <option value="0">否</option>
                        <option value="1">是</option>
                    </select>
                </div>

                <div class="data-row">
                    <h4>Q5：</h4>
                    <select v-model="data.q_5">
                        <option value="0">否</option>
                        <option value="1">是</option>
                    </select>
                </div>

                <div class="data-row">
                    <h4>說明：</h4>
                    <input type="text" v-model="data.description">
                </div>

            </div>

            <v-card-actions>
                <v-btn @click="voidInsurance" color="red">作廢</v-btn>
                <v-spacer></v-spacer>
                <v-btn @click="updateInsurance" color="success">變更</v-btn>
                <v-btn @click="show = false">關閉</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
export default {
    destroyed(){
        EventBus.$off('showDetailModal');
    },
    mounted(){
        EventBus.$on('showDetailModal',item => {
            this.id = item.id;
            this.getData();
            this.show = true;
        });
    },
    data() {
        return {
            id:null,
            data:{},
            show:false,
            statusDict:{
                'pending':'待處理',
                'processing':'處理中',
                'verified':'已核帳',
                'close':'完成',
                'void':'作廢',
            },
        };
    },
    methods:{
        getData(){
            axios.get('/api/insurance/'+this.id)
            .then(res => { 
                this.data = res.data;
            })
            .catch(error => {
                Exception.handle(error);
            })
        },
        voidInsurance(){
            axios.post(`/api/insurance/${this.id}/void`)
            .then(res => {
                this.getData();
                EventBus.$emit('reloadData');
            })
            .catch(err => {
                Exception.handle(error);
            })
        },
        updateInsurance(){
            let _data = Object.assign({},this.data);
            _data._method = 'PUT';
            axios.post(`/api/insurance/${this.id}/update`,_data)
            .then(res => {
                this.data = res.data;
                EventBus.$emit('reloadData');
            })
            .catch(err => {
                Exception.handle(error);
            })
        }
    }
}
</script>

<style>
#insurance-detail-modal input,#insurance-detail-modal select{
    padding: 4px 8px;
    border:1px solid lightgray;
    border-radius: .2rem;
    display: block;
    width: 100%;
}
</style>