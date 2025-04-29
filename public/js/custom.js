document.addEventListener('DOMContentLoaded', function () {
    const dropArea = document.getElementById('drop-area');
    const fileInput = document.getElementById('file');
    const fileInfo = document.getElementById('file-info');
    const uploadForm = document.getElementById('upload-form');
    const progressBar = document.getElementById('upload-progress');
    const progress = progressBar.querySelector('.progress-bar');
    const submitBtn = document.getElementById('submit-btn');
    const maxSize = 1 * 1024 * 1024; // 1MB

    if (!dropArea || !fileInput || !fileInfo || !uploadForm) {
        console.error('Element not found');
        return;
    }

    function formatFileSize(bytes) {
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function updateFileInfo(file) {
        if (!file) {
            fileInfo.innerHTML = `ลากและวางไฟล์ที่นี่ หรือคลิกเพื่อเลือกไฟล์<br>
            <span class="text-secondary">ไฟล์ที่อนุญาต: .xlsx, .xls, .csv | ขนาดสูงสุด 1MB</span>`;
            return;
        }

        const allowedTypes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'text/csv'
        ];

        if (!allowedTypes.includes(file.type)) {
            Swal.fire({
                icon: 'error',
                title: 'ชนิดไฟล์ไม่ถูกต้อง',
                text: 'อนุญาตเฉพาะ .xlsx, .xls, .csv เท่านั้น',
                confirmButtonColor: '#e3342f'
            });
            fileInput.value = '';
            updateFileInfo(null);
            return;
        }

        if (file.size > maxSize) {
            Swal.fire({
                icon: 'error',
                title: 'ไฟล์ใหญ่เกินไป',
                text: 'กรุณาเลือกไฟล์ไม่เกิน 1MB',
                confirmButtonColor: '#e3342f'
            });
            fileInput.value = '';
            updateFileInfo(null);
            return;
        }

        const fileSize = formatFileSize(file.size);
        const fileType = file.type || 'ไม่ทราบชนิดไฟล์';

        fileInfo.innerHTML = `
            <div class="d-flex justify-content-center align-items-center gap-2">
                <i class="fas fa-file-excel text-success me-2"></i>
                <div>
                    <strong>${file.name}</strong><br>
                    <span class="text-muted small">${fileSize} | ${fileType}</span>
                </div>
            </div>
        `;
    }

    // เลือกไฟล์จากปุ่ม
    fileInput.addEventListener('change', function () {
        const file = this.files[0];
        updateFileInfo(file);
    });

    // Drag & Drop
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, e => {
            e.preventDefault();
            e.stopPropagation();
        });
    });

    dropArea.addEventListener('dragenter', () => dropArea.classList.add('border-success'));
    dropArea.addEventListener('dragleave', () => dropArea.classList.remove('border-success'));
    dropArea.addEventListener('dragover', () => dropArea.classList.add('border-success'));

    dropArea.addEventListener('drop', (e) => {
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            updateFileInfo(files[0]);
        }
        dropArea.classList.remove('border-success');
    });

    // Submit Form
    uploadForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const file = fileInput.files[0];
        if (!file) {
            Swal.fire({
                icon: 'error',
                title: 'กรุณาเลือกไฟล์',
                text: 'กรุณาเลือกไฟล์ Excel ก่อนทำการอัปโหลด',
                confirmButtonColor: '#e3342f'
            });
            return;
        }

        if (file.size > maxSize) {
            Swal.fire({
                icon: 'error',
                title: 'ไฟล์ใหญ่เกินไป',
                text: 'กรุณาเลือกไฟล์ที่มีขนาดไม่เกิน 1MB',
                confirmButtonColor: '#e3342f'
            });
            fileInput.value = '';
            updateFileInfo(null);
            return;
        }

        const formData = new FormData(uploadForm);
        const xhr = new XMLHttpRequest();
        xhr.open('POST', uploadForm.action, true);

        progressBar.classList.remove('d-none');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> กำลังอัปโหลด...';

        xhr.upload.onprogress = function (e) {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                progress.style.width = percent + '%';
                progress.setAttribute('aria-valuenow', percent);
            }
        };

        xhr.onload = function () {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'อัปโหลดไฟล์';
            progressBar.classList.add('d-none');

            if (xhr.status === 200) {
                Swal.fire({
                    icon: 'success',
                    title: 'อัปโหลดสำเร็จ!',
                    text: 'ไฟล์ของคุณถูกอัปโหลดเรียบร้อยแล้ว',
                    confirmButtonColor: '#3aafa9'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด!',
                    text: 'ไม่สามารถอัปโหลดไฟล์ได้ กรุณาลองใหม่',
                    confirmButtonColor: '#e3342f'
                });
            }
        };

        xhr.onerror = function () {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'อัปโหลดไฟล์';
            progressBar.classList.add('d-none');

            Swal.fire({
                icon: 'error',
                title: 'เชื่อมต่อล้มเหลว',
                text: 'โปรดตรวจสอบการเชื่อมต่ออินเทอร์เน็ตของคุณ',
                confirmButtonColor: '#e3342f'
            });
        };

        xhr.send(formData);
    });
});

dropArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropArea.classList.add('dragover');
});

dropArea.addEventListener('dragleave', () => {
    dropArea.classList.remove('dragover');
});

dropArea.addEventListener('drop', (e) => {
    e.preventDefault();
    dropArea.classList.remove('dragover');
    const files = e.dataTransfer.files;
    fileInput.files = files;
    // optional: แสดงชื่อไฟล์
});
