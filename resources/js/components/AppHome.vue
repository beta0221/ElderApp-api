<template>
    <div>
        <div v-if="isLoggedIn" class="side-bar">
            <sidebar></sidebar>
        </div>
        <div class="content" :class="{ 'content-full': !isLoggedIn }">
            <router-view></router-view>
        </div>
    </div>
</template>

<script>
import sidebar from './SideBar'

export default {
    components: { sidebar },
    data() {
        return {
            isLoggedIn: User.loggedIn()
        }
    },
    watch: {
        // 每次路由切換後重新確認登入狀態（login/logout 都會觸發路由跳轉）
        $route() {
            this.isLoggedIn = User.loggedIn()
        }
    }
}
</script>

<style>
.side-bar {
    position: absolute;
    left: 0;
    top: 0;
    width: 200px;
    height: 100%;
}
.content {
    position: absolute;
    left: 200px;
    top: 0;
    height: 100%;
    width: calc(100% - 200px);
    overflow-y: scroll;
}
.content-full {
    left: 0;
    width: 100%;
}
</style>
