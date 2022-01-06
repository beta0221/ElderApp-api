<template>
    <v-dialog v-model="dialog" max-width="480px">
        
        <v-card>
            <v-card-title class="headline">輸出志工紀錄</v-card-title>
            
            <div style="padding:8px">
                <label for="">診所</label>
                <v-select v-model="clinic_slug" :items="clinics" item-text="name" item-value="slug" ></v-select>
                
                <label for="">日期區間</label><br>
                <input type="date" v-model="from_date">-<input type="date" v-model="to_date">
            </div>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn @click="exportClinicBillExcel" color="info">診所對帳表</v-btn>
                <v-btn @click="exportClinicUserSignatureExcel" color="green">志工簽名表</v-btn>
                <v-btn @click="clearQuery">清除</v-btn>
                <v-btn color="gray darken-1" flat="flat" @click="dialog = false">關閉</v-btn>
            </v-card-actions>

        </v-card>

    </v-dialog>
</template>

<script>


export default {
    components:{
        
    },
    mounted() {
        EventBus.$on("showExportPanel", () => {
            this.getClinics();
            this.dialog = true;
        });
    },
    destroyed(){
        EventBus.$off('showExportPanel');
    },
    data() {
        return {
            dialog: false,
            clinics:[],
            clinic_slug:null,
            
            from_date:null,
            to_date:null,
        };
    },
    methods:{
        getClinics(){
            axios.get('/api/clinic/all/clinic')
            .catch(error => {Exception.handle(error);})
            .then( res => {
                this.clinics = res.data;
            })
        },
        exportClinicBillExcel(){
            if(!this.clinic_slug || !this.from_date || !this.to_date){ alert('請選擇診所及日期');return; }
            let urlString = this.getUrlString('/clinic/export/clinicBill');
            window.open(urlString);
        },
        exportClinicUserSignatureExcel(){
            if(!this.clinic_slug || !this.from_date || !this.to_date){ alert('請選擇診所及日期');return; }
            let urlString = this.getUrlString('/clinic/export/signature');
            window.open(urlString);
        },
        getUrlString(pathname){
            let openUrl = new URL(window.location.href);
            openUrl.pathname = pathname;
            openUrl.searchParams.set('clinic_slug',this.clinic_slug);
            openUrl.searchParams.set('from_date',this.from_date);
            openUrl.searchParams.set('to_date',this.to_date);
            return openUrl.toString();
        },
        clearQuery(){
            this.clinic_slug = null;
            this.from_date = null;
            this.to_date = null;
        }
    }
}
</script>

<style>

</style>