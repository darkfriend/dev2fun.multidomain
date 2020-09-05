<template>
    <div
        class="adm-info-message-wrap"
        :class="type==='success' ? 'adm-info-message-green' : 'adm-info-message-red'"
    >
        <div class="adm-info-message">
            <div class="adm-info-message-title" v-html="text"></div>
            <div class="adm-info-message-icon"></div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "bxMessage",
        props: {
            type: {
                type: String,
                default() {
                    return 'success'; // or error
                }
            },
            text: String,
        },
    }
</script>

<style scoped>

</style>