<script setup lang="ts">
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { ElMessage } from 'element-plus';
import UserSelectWithCreate from '@/components/UserSelectWithCreate.vue';
import { store as enrollmentsStore } from '@/routes/enrollments';

interface Course {
    id: number;
    name: string;
}

interface User {
    id: number;
    name: string;
    email: string;
}

const props = defineProps<{
    courses: Course[];
    users: User[];
}>();

const enrollmentForm = ref();
const submitting = ref(false);

const form = reactive({
    user_id: null as number | null,
    course_id: null as number | null,
    enrollment_date: new Date().toISOString().split('T')[0],
});

const rules = {
    user_id: [
        {
            required: true,
            message: 'Please select or add a student',
            trigger: 'change',
        },
    ],
    course_id: [
        {
            required: true,
            message: 'Please select a course',
            trigger: 'change',
        },
    ],
    enrollment_date: [
        {
            required: true,
            message: 'Please select enrollment date',
            trigger: 'change',
        },
    ],
};

const submitForm = async () => {
    const valid = await enrollmentForm.value?.validate();
    if (!valid) return;

    submitting.value = true;

    router.post(enrollmentsStore.url(), form, {
        onSuccess: () => {
            ElMessage.success('Enrollment created successfully!');
            resetForm();
        },
        onError: (errors) => {
            ElMessage.error('Failed to create enrollment');
            console.error(errors);
        },
        onFinish: () => {
            submitting.value = false;
        },
    });
};

const resetForm = () => {
    form.user_id = null;
    form.course_id = null;
    form.enrollment_date = new Date().toISOString().split('T')[0];
    enrollmentForm.value?.resetFields();
};
</script>

<template>
    <Head title="Create Enrollment" />

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div
                class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800"
            >
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h1 class="mb-6 text-2xl font-bold">Student Enrollment</h1>

                    <el-form
                        ref="enrollmentForm"
                        :model="form"
                        :rules="rules"
                        label-width="120px"
                        @submit.prevent="submitForm"
                    >
                    
                        <el-form-item label="Student" prop="user_id">
                            <UserSelectWithCreate
                                v-model="form.user_id"
                                :users="users"
                                placeholder="Select existing student or add new"
                            />
                        </el-form-item>

                        <el-form-item label="Course" prop="course_id">
                            <el-select
                                v-model="form.course_id"
                                placeholder="Select course"
                                filterable
                            >
                                <el-option
                                    v-for="course in courses"
                                    :key="course.id"
                                    :label="course.name"
                                    :value="course.id"
                                />
                            </el-select>
                        </el-form-item>

                        <el-form-item
                            label="Enrollment Date"
                            prop="enrollment_date"
                        >
                            <el-date-picker
                                v-model="form.enrollment_date"
                                type="date"
                                placeholder="Select date"
                                format="YYYY-MM-DD"
                                value-format="YYYY-MM-DD"
                            />
                        </el-form-item>

                        <el-form-item>
                            <el-button
                                type="primary"
                                native-type="submit"
                                :loading="submitting"
                            >
                                Enroll Student
                            </el-button>
                            <el-button @click="resetForm">Reset</el-button>
                        </el-form-item>
                    </el-form>
                </div>
            </div>
        </div>
    </div>
</template>
