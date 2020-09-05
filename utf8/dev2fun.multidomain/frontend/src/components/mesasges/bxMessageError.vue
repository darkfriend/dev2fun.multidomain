<template>
    <bx-message
        type="error"
        :text="text"
    />
</template>

<script>
    export default {
        name: "bxMessageError",
        components: {
            bxMessage: () => import('./bxMessage'),
        },
        props: {
            text: String,
        },
    }
</script>

<style scoped>

</style>