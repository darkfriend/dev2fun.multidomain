<template>
    <table>

        <tr>
            <td width="40%">
                <label for="enable_multilang">
                    {{locale.LABEL_ENABLE_MULTILANG}}:
                </label>
            </td>
            <td width="60%">
                <input
                    type="checkbox"
                    id="enable_multilang"
                    name="enable_multilang"
                    value="Y"
                    v-model="active"
                />
            </td>
        </tr>

        <tr>
            <td width="40%">
                <label for="lang_default">
                    {{locale.LABEL_LANG_DEFAULT}}:
                </label>
            </td>
            <td width="60%">
                <input
                    type="text"
                    id="lang_default"
                    name="lang_default"
                    value=""
                    v-model="lang"
                />
            </td>
        </tr>
    </table>
</template>

<script>
    export default {
        name: "translates",
        props: {
            langActive: {
                type: Boolean,
                default() {
                    return false;
                }
            },
            langDefault: {
                type: String,
                default() {
                    return 'ru';
                }
            },
            locale: Object,
            input: Object,
        },
        data() {
            return {
                active: false,
                lang: '',
            };
        },
        created() {
            this.active = this.langActive;
            this.lang = this.langDefault;
        },
        computed: {},
        methods: {},
    }
</script>

<style scoped>

</style>