<template>
    <table
        v-if="loaded"
        class="c-table c-table--striped"
        style="max-width: 800px; margin: 0 auto;"
    >
        <thead class="c-table__head">
        <tr class="c-table__row c-table__row--heading">
            <th class="c-table__cell">urlrewrite.php</th>
            <th class="c-table__cell">
                {{ locale.LABEL_URLREWRITE_ACTION }}
            </th>
<!--            <th>After urlrewrite.php</th>-->
        </tr>
        </thead>
        <tbody class="c-table__body">

        <template v-for="item in items">
            <tr
                v-if="item.PATH !== '/index.php' && item.RULE  !== '/$2/index.php'"
                class="c-table__row"
            >
                <td class="c-table__cell">
                    <code>
                        CONDITION: {{ item.CONDITION }}<br>
                        RULE: {{ item.RULE }}<br>
                        ID: {{ item.ID }}<br>
                        PATH: {{ item.PATH }}
                    </code>
                </td>
                <td class="c-table__cell">
                    <label
                        v-if="!afterItem(item)"
                        class="c-field c-field--choice"
                    >
                        <input
                            type="checkbox"
                            value="1"
                            name="selectItems"
                            :checked="selectItems.includes(item.PATH)"
                            @change.prevent="addItem(item)"
                        /> {{ locale.LABEL_URLREWRITE_ADD_SUPPORT }}
                    </label>
                    <label
                        v-else
                        class="c-field c-field--choice"
                    >
                        <input
                            type="checkbox"
                            value="2"
                            name="selectItemsRestore"
                            :checked="selectItemsRestore.includes(item.PATH)"
                            @change.prevent="addItemRestore(item)"
                        /> {{ locale.LABEL_URLREWRITE_REMOVE_SUPPORT }}
                    </label>
                </td>
    <!--            <td class="c-table__cell">-->
    <!--                <code v-if="afterItem(item).length">-->
    <!--                    CONDITION: {{ afterItem(item).CONDITION }}<br>-->
    <!--                    RULE: {{ afterItem(item).RULE }}<br>-->
    <!--                    ID: {{ afterItem(item).ID }}<br>-->
    <!--                    PATH: {{ afterItem(item).PATH }}-->
    <!--                </code>-->
    <!--                <span v-else>-->
    <!--                    Будет создан-->
    <!--                </span>-->
    <!--            </td>-->
            </tr>

        </template>

        <tr class="c-table__row">
            <td class="c-table__cell">
                {{ locale.LABEL_URLREWRITE_MAIN_PAGE }}
            </td>
            <td class="c-table__cell">
                <label
                    v-if="!isSupportPath('/index.php')"
                    class="c-field c-field--choice"
                >
                    <input
                        type="checkbox"
                        value="1"
                        name="selectItems"
                        :checked="selectItems.includes('/index.php')"
                        @change.prevent="toggleSelectedPath('/index.php')"
                    /> {{ locale.LABEL_URLREWRITE_ADD_SUPPORT }}
                </label>
                <label
                    v-else
                    class="c-field c-field--choice"
                >
                    <input
                        type="checkbox"
                        value="2"
                        name="selectItemsRestore"
                        :checked="selectItemsRestore.includes('/index.php')"
                        @change.prevent="toggleSelectedRestorePath('/index.php')"
                    /> {{ locale.LABEL_URLREWRITE_REMOVE_SUPPORT }}
                </label>
            </td>
        </tr>
        <tr class="c-table__row">
            <td class="c-table__cell">
                Прочие страницы сайты (которые не указаны выше)
            </td>
            <td class="c-table__cell">
                <label
                    v-if="!isSupportRule('/$2/index.php')"
                    class="c-field c-field--choice"
                >
                    <input
                        type="checkbox"
                        value="1"
                        name="selectItems"
                        :checked="selectItems.includes('/$2/index.php')"
                        @change.prevent="toggleSelectedPath('/$2/index.php')"
                    /> {{ locale.LABEL_URLREWRITE_ADD_SUPPORT }}
                </label>
                <label
                    v-else
                    class="c-field c-field--choice"
                >
                    <input
                        type="checkbox"
                        value="2"
                        name="selectItemsRestore"
                        :checked="selectItemsRestore.includes('/$2/index.php')"
                        @change.prevent="toggleSelectedRestorePath('/$2/index.php')"
                    /> {{ locale.LABEL_URLREWRITE_REMOVE_SUPPORT }}
                </label>
            </td>
        </tr>

        <tr class="c-table__row">
            <td class="c-table__cell">
                <div style="margin: 0 auto; width: 50%; display: flex; justify-content: space-between;">
                    <btn
                        type="button"
                        style="margin: 0 auto; display: block;"
                        :value="btnUpdateUrlrewriteValue(siteId)"
                        :title="btnUpdateUrlrewriteValue(siteId)"
                        :class-name="'adm-btn-save'"
                        @click="updateUrlrewrite"
                    />

<!--                        <input-->
<!--                            type="button"-->
<!--                            class="adm-btn-save adm-btn-load"-->
<!--                            style="margin: 0 auto; display: block;"-->
<!--                            :value="btnUpdateUrlrewriteValue(siteId)"-->
<!--                            @click.prevent="updateUrlrewrite"-->
<!--                        />-->
<!--                        <div class="btn&#45;&#45;wrapper">-->
<!--                            <div class="adm-btn-load-img" v-show="isSubmit"></div>-->
<!--                            <input-->
<!--                                type="submit"-->
<!--                                name="save"-->
<!--                                :value="btnUpdateUrlrewriteValue(siteId)"-->
<!--                                :title="btnUpdateUrlrewriteValue(siteId)"-->
<!--                                class="adm-btn-save"-->
<!--                                :disabled="isSubmit"-->
<!--                                :class="isSubmit?'adm-btn-load':''"-->
<!--                                @click.prevent="updateUrlrewrite"-->
<!--                            />-->
<!--                        </div>-->

                    <btn
                        type="button"
                        style="margin: 0 auto; display: block;"
                        :value="btnRestoreUrlrewriteValue(siteId)"
                        :title="btnRestoreUrlrewriteValue(siteId)"
                        @click="restoreUrlrewrite"
                    />
<!--                        <input-->
<!--                            type="button"-->
<!--                            style="margin: 0 auto; display: block;"-->
<!--                            :value="btnRestoreUrlrewriteValue(siteId)"-->
<!--                            @click.prevent="restoreUrlrewrite"-->
<!--                        />-->
                </div>
            </td>
        </tr>
        </tbody>
    </table>
</template>

<script>
import http from "@/methods/http";
export default {
    name: "urlrewrite",
    components: {
        btn: () => import('../components/btn'),
    },
    props: {
        value: Object,
        locale: Object,
        siteId: {
            type: String,
            require: true,
        },
    },
    data() {
        return {
            domainKeysLocal: [],
            items: [],
            // beforeItems: [],
            afterItems: [],
            selectItems: [],
            selectItemsRestore: [],
            loaded: false,
        };
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
        isDirectoryMode() {
            return this.inputValue.logic_subdomain === 'directory';
        },
        afterItem() {
            return (item) => {
                return !this.isEmpty(
                    item.CONDITION?.match(/(\?\<subdomain\>)/s)?.[1] ?? null
                );
            }
        },
        isSupportPath() {
            return (path) => {
                return this.items
                    .filter((item) => {
                        return item.PATH === path
                            && !this.isEmpty(item.CONDITION?.match(/(\?\<subdomain\>)/s)?.[1] ?? null);
                    })
                    .length > 0;
            }
        },
        isSupportRule() {
            return (rule) => {
                return this.items
                    .filter((item) => {
                        return item.RULE === rule
                            && !this.isEmpty(item.CONDITION?.match(/(\?\<subdomain\>)/s)?.[1] ?? null);
                    })
                    .length > 0;
            };
        },
    },
    created() {
        this.init();
    },
    methods: {
        addItem(item) {
            if (!item?.PATH ?? null) {
                return;
            }
            this.toggleSelectedPath(item.PATH);
        },
        toggleSelectedPath(path) {
            if (this.selectItems.includes(path)) {
                this.selectItems.slice(
                    this.selectItems.indexOf(path),
                    1
                );
            } else {
                this.selectItems.push(path);
            }
        },
        addItemRestore(item) {
            if (!item?.PATH ?? null) {
                return;
            }
            this.toggleSelectedRestorePath(item.PATH)
        },
        toggleSelectedRestorePath(path) {
            if (this.selectItemsRestore.includes(path)) {
                this.selectItemsRestore.slice(
                    this.selectItemsRestore.indexOf(path),
                    1
                );
            } else {
                this.selectItemsRestore.push(path);
            }
        },
        async init() {
            try {
                const response = await http.post(location.href,{
                    action: 'getUrlrewrite',
                    sessid: BX.bitrix_sessid(),
                    typeSubdomain: this.inputValue.type_subdomain,
                    logicSubdomain: this.inputValue.logic_subdomain,
                    siteId: this.siteId,
                });
                if (!response?.success ?? false) {
                    throw new Error(response.msg?.exception ?? response.msg);
                }
                // this.beforeItems = response?.data?.beforeItems ?? [];
                this.afterItems = response?.data?.afterItems ?? [];
                this.items = response?.data?.items ?? [];
                this.loaded = true;
            } catch (e) {
                console.warn(e);
                this.$emit('error', e.message);
            }
        },
        btnUpdateUrlrewriteValue(siteId) {
            return `${this.locale.LABEL_URLREWRITE_UPDATE} urlrewrite (${siteId})`
        },
        updateUrlrewrite() {
            this.updateUrlrewriteAjax();
        },
        async updateUrlrewriteAjax() {
            try {
                let response = await http.post(location.href,{
                    action: 'updateUrlrewrite',
                    sessid: BX.bitrix_sessid(),
                    typeSubdomain: this.inputValue.type_subdomain,
                    logicSubdomain: this.inputValue.logic_subdomain,
                    siteId: this.siteId,
                    selectItems: this.selectItems,
                    selectItemsRestore: this.selectItemsRestore,
                });
                if (!response.success) {
                    throw new Error(response.msg.exception ?? response.msg);
                }
                this.resetSelected();
                await this.init();
                this.$forceUpdate();
                this.$emit('showMessage', response.data);
                this.$emit('saved', true, response.data);
            } catch (e) {
                console.warn(e);
                this.$emit('error', e.message);
                this.$emit('saved', false, e.message);
            } finally {

            }
        },
        btnRestoreUrlrewriteValue(siteId) {
            return `${this.locale.LABEL_URLREWRITE_RESTORE} urlrewrite (${siteId})`;
        },
        restoreUrlrewrite (siteId) {
            this.restoreUrlrewriteAjax(siteId);
        },
        async restoreUrlrewriteAjax(siteId) {
            try {
                let settings = this.settings;
                if (this.isEmpty(settings.url)) {
                    settings.url = location.href
                }
                let response = await http.post(settings.url,{
                    action: 'restoreUrlrewrite',
                    sessid: BX.bitrix_sessid(),
                    typeSubdomain: this.inputValue.type_subdomain,
                    logicSubdomain: this.inputValue.logic_subdomain,
                    siteId: siteId,
                });
                if (!response.success) {
                    throw new Error(response.msg.exception ?? response.msg);
                }
                this.resetSelected();
                await this.init();
                this.$forceUpdate();
                this.$emit('showMessage', response.data);
                this.$emit('saved', true, response.data);
            } catch (e) {
                console.warn(e);
                this.$emit('error', e.message);
                this.$emit('saved', false, e.message);
            }
        },
        resetSelected() {
            this.selectItems = [];
            this.selectItemsRestore = [];
        },
    },
}
</script>

<style scoped>

</style>