import Token from "./Token";
import AppStorage from "./AppStorage";
import router from "../router/router.js";

class User {
    login(data,from_url){
        axios.post('/api/auth/login', data)
            .then(res=>this.responseAfterLogin(res,from_url))
            .catch(error => console.log(error))
    }

    responseAfterLogin(res,from_url){
        const access_token = res.data.access_token;
        const username = res.data.name;
        if (Token.isValid(access_token)) {
            AppStorage.store(username,access_token);
            let token = `Bearer ${localStorage.getItem('token')}`;
            window.axios.defaults.headers.common['Authorization'] = token;
            router.push({path:from_url});
        }
    }

    hasToken(){
        const storedToken = AppStorage.getToken();
        if(storedToken){
            return Token.isValid(storedToken) ? true : this.logout();
        }

        return false;
    }

    loggedIn(){
        return this.hasToken();
    }

    logout(){
        AppStorage.clear();
        // window.location = '/';
        router.push({name:"login"});
    }

    name(){
        if(this.loggedIn()){
            return AppStorage.getUser()
        }
    }

    id(){
        if(this.loggedIn()){
            const payload = Token.payload(AppStorage.getToken());
            return payload.sub;
        }
    }

    own(id){
        return this.id() == id;
    }

    admin(){
        return this.id() == 13;
    }
}


export default User = new User();