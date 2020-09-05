import axios from 'axios';
// import {config} from '../../config';
// import {stringify} from 'qs';

const http = axios.create({
    // baseURL: process.env.VUE_APP_ROOT_API,
    baseURL: '/api',
    // timeout: 1000,
    // headers: {
    //     // 'Content-Type': 'application/json',
    //     'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
    // },
    // transformRequest: (data, headers) => {
    //     // console.log(data);
    //     if (data) data = stringify(data);
    //     return data;
    // }
});

http.interceptors.request.use(
    (config) => {
        // let token = false;
        // if (typeof AccessState.getters['token'] != "undefined")
        //     token = AccessState.getters['token']();
        // if (config.method == 'get') {
        //     if (token && token.length > 0) {
        //         config.params = Object.assign({}, config.params, {token: token});
        //     }
        // } else {
        //     if (token && token.length > 0)
        //         config.headers.Authorization = `Bearer ${token}`;
        // }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

export default http;
