<template>

    <v-container>
        <v-form @submit.prevent="login">
            
            <v-text-field
            label="帳號"
            v-model="form.email"
            type="email"
            required
            ></v-text-field>

            <v-text-field
            label="密碼"
            v-model="form.password"
            type="password"
            required
            ></v-text-field>

            <v-btn
            color="green"
            type="submit" class="white--text">
                登入
            </v-btn>

            <!-- <router-link to="/signup">
                <v-btn color="blue">
                    Sign Up
                </v-btn>
            </router-link> -->

        </v-form>
    </v-container>

    
</template>

<script>
export default {
    props:['from_url'],
    data(){
        return{
            form:{
                email:null,
                password:null,
            }
        }
    },
    created(){
        if(User.loggedIn()){
            this.$router.push({name:'member'});
        }
    },
    methods:{
        login(){
            User.login(this.form,this.from_url);
        }
    }
}
</script>

<style>

</style>
