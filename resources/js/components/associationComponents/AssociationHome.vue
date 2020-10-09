<template>
    <div>
        
        <div>
            <router-view></router-view>
        </div>  
        
    </div>

</template>

<script>


export default {
    created(){
        User.authOnly();
        
        this.$router.push({
            path: '/association/member'
        });
    }
}
</script>

<style>

</style>
