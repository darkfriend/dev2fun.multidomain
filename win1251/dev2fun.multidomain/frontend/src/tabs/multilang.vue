<template>
    <table class="adm-detail-content-table edit-table" id="edit3_edit_table" v-if="load">
        <tbody>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l">
                <label for="enable_multilang">
                    {{locale.LABEL_ENABLE_MULTILANG}}:
                </label>
            </td>
            <td width="60%" class="adm-detail-content-cell-r">
                <input
                    type="checkbox"
                    id="enable_multilang"
                    name="enable_multilang"
                    value="Y"
                    v-model="inputValue.enable_multilang"
                />
            </td>
        </tr>

        <tr>
            <td width="40%" class="adm-detail-content-cell-l">
                <label for="lang_default">
                    {{locale.LABEL_LANG_DEFAULT}}:
                </label>
            </td>
            <td width="60%" class="adm-detail-content-cell-r">
                <input
                    type="text"
                    id="lang_default"
                    name="lang_default"
                    v-model="inputValue.lang_default"
                />
            </td>
        </tr>

        <tr class="heading">
            <td colspan="2">
                <b>{{locale.D2F_MULTIDOMAIN_LABEL_SUPPORT_TRANSLATE}}</b>
            </td>
        </tr>
        <tr v-for="(field, key) in inputValue.lang_fields">
            <td width="40%" class="adm-detail-content-cell-l">
                <label for="lang_default">
                    {{locale.LABEL_LANG_SUPPORT_FIELDS}}:
                </label>
            </td>
            <td width="60%" class="adm-detail-content-cell-r">
                <select
                    type="text"
                    v-model="inputValue.lang_fields[key].iblock"
                    @change="changeIblock(key)"
                >
                    <optgroup v-for="group in iblocks().groups" :label="group.label">
                        <option
                            v-for="iblock in iblocks().items"
                            v-if="iblock.group==group.id"
                            :value="iblock.id"
                        >{{iblock.label}}</option>
                    </optgroup>
                </select>
                <select
                    type="text"
                    v-model="inputValue.lang_fields[key].fieldType"
                    @change="changeFieldType(key)"
                    :placeholder="locale.D2F_MULTIDOMAIN_PLACEHOLDER_TYPE"
                    :disabled="isEmpty(inputValue.lang_fields[key].iblock)"
                >
                    <option value=""></option>
                    <option
                        v-for="type in fieldTypes"
                        :value="type"
                    >
                        {{type}}
                    </option>
                </select>
                <select
                    type="text"
                    v-model="inputValue.lang_fields[key].field"
                >
                    <optgroup v-for="group in fieldsByIndexLocal[key].groups" :label="group.label">
                        <option
                            v-for="field in fieldsByIndexLocal[key].items"
                            v-if="field.group==group.id"
                            :value="field.id"
                        >{{field.label}}</option>
                    </optgroup>
                </select>
                <input type="button" :value="locale.D2F_MULTIDOMAIN_LABEL_DELETE" @click.prevent="removeRow(key)">
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <input type="button" :value="locale.LABEL_ADD" @click.prevent="addSupportLangField">
            </td>
        </tr>

        </tbody>
    </table>
</template>

<script>
    import http from "../methods/http";

    export default {
        name: "multilang",
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
            settings: Object,
            value: Object,
        },
        data() {
            return {
                load: false,
                active: false,
                lang: '',
                iblocksLocal: [],
                fieldsLocal: [],
                fieldsByIndexLocal: [],
                fieldsSectionLocal: [],
                fieldsSectionByIndexLocal: [],
                fieldTypes: [
                    'element',
                    'section',
                ],
            };
        },
        async created() {
            this.active = this.langActive;
            this.lang = this.langDefault;
            if(!this.isEmpty(this.inputValue.lang_fields)) {
                await this.getIblocks();
                for(let key in this.inputValue.lang_fields) {
                    let field = this.inputValue.lang_fields[key];
                    if(field.fieldType==='section') {
                        await this.getFieldsSection(key, field.iblock);
                    } else {
                        await this.getFields(key, field.iblock);
                    }
                }
            }
            await this.$nextTick();
            this.load = true;
        },
        mounted() {
            if(typeof this.inputValue.lang_fields != 'object') {
                this.inputValue.lang_fields = [];
            }
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
        methods: {
            iblocks() {
                if(!this.isEmpty(this.iblocksLocal)) {
                    return this.iblocksLocal;
                } else {
                    this.getIblocks();
                }
                return this.iblocksLocal;
            },
            fields(key) {
                if(this.isEmpty(key) && key!=0) {
                    return [];
                }
                if(this.isEmpty(this.inputValue.lang_fields[key]) || this.isEmpty(this.inputValue.lang_fields[key].iblock)) {
                    return [];
                }
                let id = this.inputValue.lang_fields[key].iblock;
                if(!id) return [];
                if(!this.isEmpty(this.fieldsLocal[id])) {
                    this.$set(
                        this.fieldsByIndexLocal,
                        key,
                        this.fieldsLocal[id]
                    );
                    return this.fieldsLocal[id];
                }

                this.getFields(id);

                setTimeout(()=>{
                    this.$set(
                        this.fieldsByIndexLocal,
                        key,
                        this.fieldsLocal[id]
                    );
                },1000);

                return this.fieldsLocal[id];
            },
            async getIblocks() {
                if(!this.isEmpty(this.iblocksLocal)) {
                    return this.iblocksLocal;
                }
                try {
                    let response = await http.post(this.settings.url,{
                        action: 'getIblocks',
                        sessid: this.settings.sessid,
                    });
                    if (!response.success) {
                        throw new Error(response.msg.exception ?? response.msg);
                    }
                    this.iblocksLocal = response.data;
                } catch (e) {
                    this.$emit('error', e.message);
                }
                return this.iblocksLocal;
            },
            async getFields(key, id=null) {
                if(!id) {
                    if(this.isEmpty(key) && key!=0) {
                        return;
                    }
                    if(this.isEmpty(this.inputValue.lang_fields[key])) {
                        return;
                    }
                    if(this.isEmpty(this.inputValue.lang_fields[key]).iblock) {
                        return;
                    }
                    id = this.inputValue.lang_fields[key].iblock;
                }

                if(!this.isEmpty(this.fieldsLocal[id])) {
                    this.$set(
                        this.fieldsByIndexLocal,
                        key,
                        this.fieldsLocal[id]
                    );
                    return this.fieldsLocal[id];
                }
                try {
                    let response = await http.post(this.settings.url,{
                        // params: {
                        action: 'getFields',
                        id: id,
                        sessid: this.settings.sessid,
                        // }
                    });
                    if (!response.success) {
                        throw new Error(response.msg);
                    }
                    this.fieldsLocal[id] = response.data;
                    this.$set(
                        this.fieldsByIndexLocal,
                        key,
                        response.data
                    );
                    return this.fieldsLocal[id];
                } catch (e) {
                    this.$emit('error', e.message);
                    // notify.error(e.message);
                }
            },
            async getFieldsSection(key, id=null) {
                // inputValue.lang_fields[key].iblock
                // console.log('key='+key);
                if(!id) {
                    if(this.isEmpty(key) && key!=0) {
                        return;
                    }
                    if(this.isEmpty(this.inputValue.lang_fields[key])) {
                        return;
                    }
                    if(this.isEmpty(this.inputValue.lang_fields[key]).iblock) {
                        return;
                    }
                    id = this.inputValue.lang_fields[key].iblock;
                }

                if(!this.isEmpty(this.fieldsSectionLocal[id])) {
                    this.$set(
                        this.fieldsByIndexLocal,
                        key,
                        this.fieldsSectionLocal[id]
                    );
                    return this.fieldsSectionLocal[id];
                }
                try {
                    let response = await http.post(this.settings.url,{
                        action: 'getFieldsSection',
                        id: id,
                        sessid: this.settings.sessid,
                    });
                    if (!response.success) {
                        throw new Error(response.msg);
                    }
                    this.fieldsSectionLocal[id] = response.data;
                    this.$set(
                        this.fieldsByIndexLocal,
                        key,
                        response.data
                    );
                    return this.fieldsSectionLocal[id];
                } catch (e) {
                    this.$emit('error', e.message);
                }
            },
            addSupportLangField() {
                this.inputValue.lang_fields.push({
                    iblock: '',
                    field: '',
                });
                this.fieldsByIndexLocal.push({
                    groups: [],
                    items: [],
                });
                this.$forceUpdate();
            },
            changeIblock(key) {
                this.$forceUpdate();
                // setTimeout(()=>{
                //     this.getFields(key);
                // }, 1000);
                // this.$nextTick(()=>{
                //     console.log(key);
                //     this.getFields(key);
                // });
            },
            changeFieldType(key) {
                setTimeout(()=>{
                    if(this.inputValue.lang_fields[key].fieldType==='section') {
                        this.getFieldsSection(key);
                    } else {
                        this.getFields(key);
                    }
                }, 1000);
            },
            removeRow(key) {
                this.inputValue.lang_fields.splice(key, 1);
                this.fieldsByIndexLocal.splice(key, 1);
            },
        },
    }
</script>

<style scoped>

</style>