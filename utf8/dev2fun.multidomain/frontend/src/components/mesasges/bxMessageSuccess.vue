<template>
    <bx-message
        type="success"
        :text="text"
    />
</template>

<script>
    export default {
        name: "bxMessageSuccess",
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