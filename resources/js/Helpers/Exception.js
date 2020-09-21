import User from './User';

class Exception{
    handle(error){
        if(this.isTokenInvalid(error.response.data.error)){ return; }
        if(this.isExpired(error.response.data.error)){ return; }
        if(this.AdminOnly(error.response.data)){ return; }
        alert(error.response.data);
    }

    isTokenInvalid(error){
        if(error == 'Token is invalid'){
            let from_url = window.location.pathname;
            User.logout(from_url);
            return true;
        }
        return false;
    }

    isExpired(error){
        if (error == 'Token is expired') {
            let from_url = window.location.pathname;
            User.logout(from_url);
            return true;
        }
        return false;
    }

    AdminOnly(error){
        if(error == 'admin only'){
            alert('權限不足');
            return true;
        }
        return false;
    }
}

export default Exception = new Exception()