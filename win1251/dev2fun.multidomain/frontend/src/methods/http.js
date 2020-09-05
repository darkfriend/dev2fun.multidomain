import axios from 'axios';
// import {Token} from './token';
// import {CSRF} from './csrf';
// import {AccessState} from '../store/modules/AccessState';
// import {configMain} from './configMain';
import {stringify} from 'qs';

const http = axios.create({
    // baseURL: process.env.VUE_APP_ROOT_API,
    // baseURL: configMain.url(),
    timeout: 30000,
    headers: {
    //     'Content-Type': 'application/json',
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
    },
    transformRequest: (data, headers) => {
        // console.log(data);
        if (data) data = stringify(data);
        console.log(data);
        return data;
    }
});

http.interceptors.request.use(
    (config) => {
        // let token = null;
        // if(Token.checkToken()) {
        //     // token = Token.getToken();
        //     config.headers.Authorization = `Bearer ${Token.getToken()}`;
        // }

        // if (config.method === 'post') {
            // config.params = Object.assign({}, config.params, {token: token});
        // config.headers['X-CSRF-Token'] = CSRF.get();
        // }

        // if(config.headers['X-CSRF-Token'])

        // if (typeof AccessState.getters['token'] != "undefined") {
        //     token = AccessState.getters['token']();
        // }
        // if (config.method == 'get') {
        //     if (token && token.length > 0) {
        //         config.params = Object.assign({}, config.params, {token: token});
        //     }
        // } else {
        // if (token && token.length > 0) {
        //     config.headers.Authorization = `Bearer ${token}`;
        // }
        // }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

http.interceptors.response.use(
    response => {
        return response.data
        // if (res.code === 50008 || res.code === 50012 || res.code === 50014) {
        //     // to re-login
        //     MessageBox.confirm('You have been logged out, you can cancel to stay on this page, or log in again', 'Confirm logout', {
        //         confirmButtonText: 'Re-Login',
        //         cancelButtonText: 'Cancel',
        //         type: 'warning'
        //     }).then(() => {
        //         store.dispatch('user/resetToken').then(() => {
        //             location.reload()
        //         })
        //     })
        // }
        // return Promise.reject(new Error(res.message || 'Error'))
    },
    error => {
        // Message({
        //     message: error.message,
        //     type: 'error',
        //     duration: 5 * 1000
        // })
        return Promise.reject(error)
    }
);

export default http;
