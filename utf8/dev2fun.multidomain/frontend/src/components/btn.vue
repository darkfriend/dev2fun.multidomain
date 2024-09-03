<template>
    <div class="btn--wrapper">
        <div class="adm-btn-load-img" v-show="loader"></div>
        <input
            :type="type"
            :name="name"
            :value="value"
            :title="title"
            :disabled="loader || disabled"
            :class="getClass"
            @click.prevent="$emit('click')"
        />
    </div>
</template>

<script>
export default {
    name: "btn",
    props: {
        type: String,
        name: String,
        loader: Boolean,
        disabled: Boolean,
        value: String,
        title: String,
        className: String | Object,
        // default: () => 'adm-btn-save',
    },
    computed: {
        getClass() {
            let result
            if (typeof this.className === 'object') {
                result = this.className;
                result['adm-btn-load'] = this.loader;
                // if (this.)
            } else {
                result = this.className;
                if (this.loader) {
                    result += ' adm-btn-load';
                }
            }
            return result;
        },
    },
}
</script>

<style scoped>
.btn--wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
}
.btn--wrapper .adm-btn-load-img{
    z-index: 1;
    left: 50%;
    top: 50%;
    margin-left: -10px;
    margin-top: -10px;
}
</style>