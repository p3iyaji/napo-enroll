<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import axios from 'axios';
import { ElMessage } from 'element-plus';

interface User {
    id: number;
    name: string;
    email: string;
}

const props = withDefaults(
    defineProps<{
        modelValue: number | string | null;
        placeholder?: string;
        /** When provided (e.g. from Inertia), options are shown without calling an API. */
        users?: User[];
    }>(),
    { users: () => [] },
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: number | string | null): void;
}>();

const userOptions = ref<User[]>([]);

function sameUserId(a: number | string, b: number | string): boolean {
    return Number(a) === Number(b);
}

watch(
    () => props.users,
    (users) => {
        userOptions.value = users?.length ? [...users] : [];
    },
    { immediate: true },
);

/** Coerce Inertia/string ids so el-option :value (number) matches and the label shows. */
const selectModel = computed<number | undefined>({
    get() {
        const v = props.modelValue;
        if (v === null || v === undefined || v === '') {
            return undefined;
        }
        const n = Number(v);
        return Number.isFinite(n) ? n : undefined;
    },
    set(v: number | string | null | undefined) {
        if (v === null || v === undefined || v === '') {
            emit('update:modelValue', null);
            return;
        }
        const n = Number(v);
        emit('update:modelValue', Number.isFinite(n) ? n : null);
    },
});

/** Include the current selection in the list so the select can resolve its label. */
const displayUsers = computed(() => {
    const list = [...userOptions.value];
    const id = selectModel.value;
    if (id === undefined) {
        return list;
    }
    if (list.some((u) => sameUserId(u.id, id))) {
        return list;
    }
    const fromProps = props.users?.find((u) => sameUserId(u.id, id));
    if (fromProps) {
        list.unshift(fromProps);
    }
    return list;
});

const loading = ref(false);
const dialogVisible = ref(false);
const userForm = ref();
const newUser = ref({
    name: '',
    email: '',
});

const rules = {
    name: [{ required: true, message: 'Please enter name', trigger: 'blur' }],
    email: [
        {
            required: true,
            type: 'email',
            message: 'Please enter valid email',
            trigger: 'blur',
        },
    ],
};

const loadUsers = async () => {
    if (userOptions.value.length > 0) {
        return;
    }

    loading.value = true;
    try {
        const response = await axios.get('/api/users/list');
        userOptions.value = response.data;
    } catch {
        ElMessage.error('Failed to load users');
    } finally {
        loading.value = false;
    }
};

const openAddUserDialog = () => {
    dialogVisible.value = true;
};

const createUser = async () => {
    const valid = await userForm.value?.validate();
    if (!valid) return;

    try {
        const response = await axios.post('/users', newUser.value);
        const created = response.data as Partial<User>;
        const id = Number(created.id);
        if (!Number.isFinite(id)) {
            ElMessage.error('Could not read new user from server.');
            return;
        }
        const entry: User = {
            id,
            name: created.name ?? newUser.value.name,
            email: created.email ?? newUser.value.email,
        };
        userOptions.value.push(entry);
        emit('update:modelValue', entry.id);
        dialogVisible.value = false;
        newUser.value = { name: '', email: '' };
        ElMessage.success('User created successfully');
    } catch (error: any) {
        ElMessage.error(
            error.response?.data?.message || 'Failed to create user',
        );
    }
};
</script>

<template>
    <div>
        <el-select
            v-model="selectModel"
            class="w-full"
            :placeholder="placeholder"
            filterable
            clearable
            :loading="loading"
            @focus="loadUsers"
        >
            <el-option
                v-for="user in displayUsers"
                :key="user.id"
                :label="`${user.name} (${user.email})`"
                :value="Number(user.id)"
            />
            <template #footer>
                <el-button
                    link
                    type="primary"
                    class="create-option w-full justify-start px-3 py-2"
                    @click.stop="openAddUserDialog"
                >
                    + Add new student
                </el-button>
            </template>
        </el-select>

        <el-dialog v-model="dialogVisible" title="Add New User" width="30%">
            <el-form :model="newUser" :rules="rules" ref="userForm">
                <el-form-item label="Name" prop="name">
                    <el-input v-model="newUser.name" />
                </el-form-item>
                <el-form-item label="Email" prop="email">
                    <el-input v-model="newUser.email" />
                </el-form-item>
            </el-form>
            <template #footer>
                <el-button @click="dialogVisible = false">Cancel</el-button>
                <el-button type="primary" @click="createUser">Create</el-button>
            </template>
        </el-dialog>
    </div>
</template>

<style scoped>
.create-option {
    color: #409eff;
    font-weight: bold;
}
</style>
