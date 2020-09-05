<template>
    <div>
        <h2>{{locale.DOMAIN_LIST_H2}}</h2>
        <div class="adm-info-message-wrap">
            <div class="adm-info-message" v-html="locale.D2F_MULTIDOMAIN_SUBDOMAIN_LIST_NOTE"></div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "domains",
        props: {
            settings: Object,
            value: Object,
            locale: Object,
        },
        data(){
            return {};
        },
        computed: {
            inputValue: {
                get() {
                    return this.value;
                },
                set(val) {
                    this.$emit('input', val);
                }
            },
        },
    }
</script>

<style scoped>

</style>