<template>
    <div>

        <bx-message
            v-if="resultMessage.show"
            :type="resultMessage.success?'success':'error'"
            :text="resultMessage.text"
        />

        <bx-tabs
            :selected="tabSelect"
            :form-settings="formSettings"
            :is-submit="isSubmit"
            @save="save"
            @apply="save"
        >
            <bx-tab
                v-for="tab in tabs"
                :id="tab.id"
                :key="tab.key"
                :name="tab.key"
                :header="tab.label"
                :header-title="tab.title"
                :title="tab.detailTitle"
            >
                <settings
                    v-if="tab.key==='settings'"
                    :settings="settings"
                    :locale="locale"
                    v-model="inputValue"
                />
                <domains
                    v-else-if="tab.key==='domains'"
                    v-model="inputValue"
                    :locale="locale"
                />
                <multilang
                    v-else-if="tab.key==='multilang'"
                    v-model="inputValue"
                    :locale="locale"
                    :settings="{
                        url: formData.action,
                        sessid: formData.sessid,
                    }"
                />
                <seo
                    v-else-if="tab.key==='seo'"
                    v-model="inputValue"
                    :locale="locale"
                />
                <donate
                    v-else-if="tab.key==='donate'"
                    :locale="locale"
                />
                <div v-else>
                    {{tab.content}}
                </div>
            </bx-tab>
        </bx-tabs>
    </div>
</template>
<script>
    import http from "./methods/http";

    export default {
        // name: 'app',
        components: {
            settings: () => import('./tabs/settings'),
            multilang: () => import('./tabs/multilang'),
            domains: () => import('./tabs/domains'),
            seo: () => import('./tabs/seo'),
            donate: () => import('./tabs/donate'),
        },
        props: {
            inputValue: {
                type: Object,
                // require: true,
                default() {
                    return {
                        // settings
                        logic_subdomain: 'virtual',
                        type_subdomain: 'city',
                        key_ip: 'REMOTE_ADDR',
                        domain_default: location.host,
                        MAPLIST: [
                            {
                                KEY: '',
                                SUBNAME: '',
                            }
                        ],
                        EXCLUDE_PATH: [
                            '\/(bitrix|local)\/(admin|tools)\/',
                            '',
                        ],
                        // domains
                        // multilang
                        enable_multilang: false,
                        lang_default: 'ru',
                        lang_fields: [],
                        // seo
                        enable_seo_page: false,
                        enable_seo_title_add_city: false,
                        pattern_seo_title_add_city: '#TITLE# - #CITY#',
                    };
                }
            },
            formSettings: {
                type: Object,
                default() {
                    return {};
                }
            },
            settings: {
                type: Object,
                default() {
                    return {
                        remoteAddr: '',
                        realIp: '',
                    };
                }
            },
            locale: Object,
        },
        data() {
            return {
                isSubmit: false,
                tabSelectAll: false,
                tabSelect: '#settings',
                resultMessage: {
                    show: false,
                    success: false,
                    text: '',
                },
            }
        },
        computed: {
            tabs() {
                return [
                    {
                        key: 'settings',
                        id: 'editSettings',
                        title: this.locale.MAIN_TAB_SET,
                        label: this.locale.D2F_MULTIDOMAIN_MAIN_TAB_SETTINGS,
                        detailTitle: this.locale.MAIN_TAB_TITLE_SET,
                        content: this.locale.D2F_MULTIDOMAIN_MAIN_TAB_SETTINGS,
                    },
                    {
                        key: 'domains',
                        id: 'editDomains',
                        title: this.locale.D2F_MULTIDOMAIN_TAB_2,
                        label: this.locale.D2F_MULTIDOMAIN_TAB_2,
                        detailTitle: this.locale.D2F_MULTIDOMAIN_TAB_2_TITLE_SET,
                        content: this.locale.D2F_MULTIDOMAIN_TAB_2,
                    },
                    {
                        key: 'multilang',
                        id: 'editMultilang',
                        title: this.locale.D2F_MULTIDOMAIN_TAB_3,
                        label: this.locale.D2F_MULTIDOMAIN_TAB_3,
                        detailTitle: this.locale.D2F_MULTIDOMAIN_TAB_3_TITLE_SET,
                        content: this.locale.D2F_MULTIDOMAIN_TAB_3_TITLE_SET,
                    },
                    {
                        key: 'seo',
                        id: 'editSEO',
                        title: 'SEO',
                        label: 'SEO',
                        detailTitle: this.locale.D2F_MULTIDOMAIN_TAB_4_TITLE_SET,
                        // detailTitle: 'Настройка SEO',
                        content: 'SEO',
                    },
                    {
                        key: 'donate',
                        id: 'editDonate',
                        title: this.locale.SEC_DONATE_TAB,
                        label: this.locale.SEC_DONATE_TAB,
                        detailTitle: this.locale.SEC_DONATE_TAB_TITLE,
                        content: this.locale.SEC_DONATE_TAB,
                    },
                ];
            },
            expandLinkData() {
                let result = {};
                if(this.tabSelectAll) {
                    result = {
                        title: this.locale.D2F_MULTIDOMAIN_LABEL_TAB_SELECT_ALL,
                    };
                } else {
                    result = {
                        title: this.locale.D2F_MULTIDOMAIN_LABEL_TAB_COLLAPSE,
                    };
                }
                return result;
            },
            formData() {
                return Object.assign(
                    {},
                    {
                        method: 'post',
                        action: '/bitrix/admin/settings.php?mid=dev2fun.multidomain&lang=ru&tabControl_active_tab=edit1',
                        enctype: 'multipart/form-data',
                        name: 'editform',
                        class: 'editform',
                        sessid: '',
                    },
                    this.formSettings
                );
            },
        },
        methods: {
            async save() {
                this.isSubmit = true;
                try {
                    let response = await http.post(
                        this.formData.action,
                        Object.assign(
                            {},
                            this.inputValue,
                            {
                                sessid: this.formData.sessid,
                                action: 'save',
                            }
                        )
                    );
                    if (!response.success) {
                        throw new Error(response.msg);
                    }
                    this.resultMessage.success = true;
                    this.resultMessage.text = response.msg;
                } catch (e) {
                    console.warn(e.message);
                    this.resultMessage.text = e.message;
                }
                this.resultMessage.show = true;
                this.isSubmit = false;
            },
            isActive(key) {
                return key === this.tabSelect;
            },
            tabClass(key) {
                if (this.isActive(key) && !this.tabSelectAll) {
                    return 'adm-detail-tab-active';
                }
                return '';
            },
            selectTab(key) {
                this.tabSelect = key;
            },
            setShowAll(action=true) {
                this.tabSelectAll = action;
            },
            tabContent(tab) {
                if(!this.isEmpty(tab.component)) {
                    return tab.component();
                }
                if(!this.isEmpty(tab.content)) {
                    return tab.content;
                }
                return '';
            },
        },
    };
</script>