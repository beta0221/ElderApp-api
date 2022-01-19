<template>
    <table>
        <tr>
            <th>數量</th>
            <th>單價</th>
            <th>團購價</th>
            <th>平台手續費(%)</th>
            <th>協會回饋(%)</th>
            <th>團嗎回饋(%)</th>
            <th>市價</th>
            <th>-</th>
        </tr>
        <tr v-for="(p,index) in packages" v-bind:key="p.id">
            <td>
                <v-text-field solo type="number" v-model="p.quantity"/>
            </td>
            <td>
                <v-text-field solo type="number" v-model="p.price_per_item"/>
            </td>
            <td>
                <v-text-field solo type="number" v-model="p.price"/>
            </td>
            <td>
                <v-text-field solo type="number" v-model="p.platform_fee_rate"/>
            </td>
            <td>
                <v-text-field solo type="number" v-model="p.organization_fee_rate"/>
            </td>
            <td>
                <v-text-field solo type="number" v-model="p.host_bonus_rate"/>
            </td>
            <td>
                <v-text-field solo type="number" v-model="p.market_price"/>
            </td>
            <td><v-btn color="error" @click="removePackage(index)">刪除</v-btn></td>
        </tr>

        <tr>
            <td>
                <v-text-field solo type="number" v-model="newPackage.quantity"/>
            </td>
            <td>
                <v-text-field solo type="number" v-model="newPackage.price_per_item"/>
            </td>
            <td>
                <v-text-field solo type="number" v-model="newPackage.price"/>
            </td>
            <td>
                <v-text-field solo type="number" v-model="newPackage.platform_fee_rate"/>
            </td>
            <td>
                <v-text-field solo type="number" v-model="newPackage.organization_fee_rate"/>
            </td>
            <td>
                <v-text-field solo type="number" v-model="newPackage.host_bonus_rate"/>
            </td>
            <td>
                <v-text-field solo type="number" v-model="newPackage.market_price"/>
            </td>
            <td><v-btn color="success" @click="addPackage">新增</v-btn></td>
        </tr>

    </table>
</template>

<script>
export default {
    props:['packages'],
    data(){
        return{
            newPackage:{}
        }
    },
    methods:{
        addPackage(){
            let _newPackage = Object.assign({},this.newPackage);
            Object.keys(_newPackage).forEach(key =>{
                _newPackage[key] = parseInt(_newPackage[key]);
            })
            this.packages.push(_newPackage);
            this.newPackage = {};
        },
        removePackage(index){
            this.packages.splice(index,1);
        }
    }

}
</script>

<style>

</style>