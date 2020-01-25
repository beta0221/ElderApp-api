import User from './User';

class Exception{
    handle(error){
        this.isTokenInvalid(error.response.data.error);
        this.isExpired(error.response.data.error);
        this.AdminOnly(error.response.data);
    }

    isTokenInvalid(error){
        if(error == 'Token is invalid'){
            let from_url = window.location.pathname;
            User.logout(from_url);
        }
    }

    isExpired(error){
        if (error == 'Token is expired') {
            let from_url = window.location.pathname;
            User.logout(from_url);
        }
    }

    AdminOnly(error){
        if(error == 'admin only'){
            alert('權限不足');
        }
    }
}

export default Exception = new Exception()