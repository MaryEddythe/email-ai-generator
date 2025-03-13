<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Callbox Email Generator</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg" style="background-color: #0f0f17;">
        <div class="container-fluid">
            <a class="navbar-brand" href="#" style="color: var(--bs-yellow);">Callbox Email Generator</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>

    <div class="container mt-5">  
        <!-- Form and Results Container -->
        <div class="form-results-container row">
            <div class="form-container col-md-6">
                <div class="card p-4">
                    <form id="emailForm">
                        <div class="form-group">
                            <label for="subject">Enter Email Subject:</label>
                            <input type="text" id="subject" name="subject" class="form-control" placeholder="Enter email subject..." required>
                        </div>
                        <div class="form-group">
                            <label for="keywords">Enter Keywords (Optional):</label>
                            <input type="text" id="keywords" name="keywords" class="form-control" placeholder="Include keywords...">
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Generate Email</button>
                    </form>
                </div>
            </div>
    
            <div class="results-container col-md-6">
                <div id="generatedEmailSection" class="card p-4">
                    <h3>Generated Email</h3>
                    <p id="generatedEmail">Your email content will appear here...</p>
                </div>
            </div>
        </div>
        <div id="loadingSpinner" class="text-center hidden">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <p class="mt-2">Generating email...</p>
        </div>
    
        <!-- Show Saved Emails Button -->
        <div class="text-center mt-4">
            <button id="toggleSavedEmails" class="btn btn-success" onclick="toggleSavedEmails()"><i class="fas fa-save"></i> Show Saved Emails</button>
        </div>

        <!-- Edit Email Modal -->
        <div id="editModal" class="modal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Edit Email</h5>
                        <span class="close" onclick="closeEditModal()">&times;</span>
                    </div>
                    <div class="modal-body">
                        <form id="editEmailForm">
                            <input type="hidden" id="editEmailId">
                            <div class="form-group">
                                <label for="editEmailContent">Email Content:</label>
                                <textarea id="editEmailContent" class="form-control" rows="10"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Close</button>
                        <button type="button" class="btn btn-primary" onclick="saveEmail()">Save changes</button>
                    </div>
                </div>
            </div>
        </div>
    
        <!-- Saved Emails Section -->
        <div id="savedEmailsSection" class="card saved-emails-section hidden mt-4">
            <div class="card-body">
                <h3>Saved Emails</h3>
                <!-- Search Bar -->
                <div class="search-bar mb-4">
                    <div class="input-group">
                        <input type="text" id="searchEmails" class="form-control" placeholder="Search emails by subject..." oninput="filterEmails()">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" onclick="clearSearch()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <ul id="savedEmails" class="list-group">
                    @if ($emails->isEmpty())
                        <li class="list-group-item text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-3"></i>
                            <p>No saved emails found.</p>
                        </li>
                    @else
                        @foreach ($emails as $email)
                            <li class="list-group-item email-item">
                                <div class="email-header">
                                    <h4 class="email-subject">{{ $email->subject }}</h4>
                                    <div class="email-actions">
                                        <button class="btn btn-warning btn-sm edit-button" onclick="editEmail('{{ $email->id }}', '{{ $email->subject }}', `{{ $email->content }}`)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-info btn-sm read-more-button" onclick="toggleFullEmail('{{ $email->id }}')">
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm remove-button" onclick="removeEmail('{{ $email->id }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="email-content">
                                    <p id="emailContentPreview{{ $email->id }}" class="email-preview">{{ implode(' ', array_slice(explode(' ', $email->content), 0, 30)) }}...</p>
                                    <p id="emailContentFull{{ $email->id }}" class="email-full hidden">{{ $email->content }}</p>
                                </div>
                            </li>
                        @endforeach
                    @endif
                </ul>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.getElementById('emailForm').addEventListener('submit', function(event) {
            event.preventDefault(); 
            document.getElementById('loadingSpinner').classList.remove('hidden');
            document.getElementById('generatedEmail').innerText = '';

            let subject = document.getElementById('subject').value;
            let keywords = document.getElementById('keywords').value;
    
            swal.fire({
                title: 'Generating Email...',
                text: 'Please wait while the email is being generated.',
                icon: 'info',
                allowOutsideClick: false,
                didOpen: () => {
                    swal.showLoading();
                }
            });
    
            axios.post('/generate-email', { subject: subject, keywords: keywords })
                .then(response => {
                    swal.fire({
                        title: 'Email Generated',
                        text: 'The email has been successfully generated.',
                        icon: 'success'
                    });
                    document.getElementById('generatedEmail').innerText = response.data.email_content;
                    adjustContainerHeights(); 
                    fetchEmails();
                })
                .catch(error => {
                    swal.fire({
                        title: 'Error',
                        text: 'Failed to generate email. Please try again.',
                        icon: 'error'
                    });
                    console.error('Error:', error);
                });
        });
    
        function fetchEmails() {
            axios.get('/emails')
                .then(response => {
                    let emailList = document.getElementById('savedEmails');
                    emailList.innerHTML = '';
                    response.data.forEach(email => {
                        let listItem = document.createElement('li');
                        listItem.className = 'list-group-item email-item';
                        let preview = email.content.split(' ').slice(0, 30).join(' ') + '...';
                        listItem.innerHTML = `
                            <div class="email-header">
                                <h4 class="email-subject">${email.subject}</h4>
                                <div class="email-actions">
                                    <button class="btn btn-warning btn-sm edit-button" onclick="editEmail('${email.id}', '${email.subject}', \`${email.content.replace(/`/g, '\\`').replace(/'/g, "\\'")}\`)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-info btn-sm read-more-button" onclick="toggleFullEmail('${email.id}')">
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm remove-button" onclick="removeEmail('${email.id}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="email-content">
                                <p id="emailContentPreview${email.id}" class="email-preview">${preview}</p>
                                <p id="emailContentFull${email.id}" class="email-full hidden">${email.content}</p>
                            </div>
                        `;
                        emailList.appendChild(listItem);
                    });
                })
                .catch(error => console.error('Error:', error));
        }

        function toggleFullEmail(emailId) {
            console.log(`Toggling email with ID: ${emailId}`);

            const previewElement = document.getElementById(`emailContentPreview${emailId}`);
            const fullElement = document.getElementById(`emailContentFull${emailId}`);
            
            if (!previewElement || !fullElement) {
                console.error(`Elements not found for email ID: ${emailId}`);
                return;
            }
            
            const emailItem = previewElement.closest('.email-item');
            const readMoreButton = emailItem.querySelector('.read-more-button i');
            
            if (!readMoreButton) {
                console.error('Read more button not found!');
                return;
            }
            
            if (fullElement.classList.contains('hidden')) {
                previewElement.classList.add('hidden');
                fullElement.classList.remove('hidden');
                readMoreButton.classList.remove('fa-chevron-down');
                readMoreButton.classList.add('fa-chevron-up');
            } else {
                previewElement.classList.remove('hidden');
                fullElement.classList.add('hidden');
                readMoreButton.classList.remove('fa-chevron-up');
                readMoreButton.classList.add('fa-chevron-down');
            }
        }

        function clearSearch() {
            document.getElementById('searchEmails').value = '';
            filterEmails();
        }

        function filterEmails() {
            const searchTerm = document.getElementById('searchEmails').value.toLowerCase();
            const emailItems = document.querySelectorAll('.email-item');

            emailItems.forEach(item => {
                const subject = item.querySelector('.email-subject').textContent.toLowerCase();
                if (subject.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }
    
        function editEmail(emailId, subject, content) {
            document.getElementById('editEmailId').value = emailId;
            document.getElementById('editEmailContent').value = content.replace(/\\'/g, "'");
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
    
        function saveEmail() {
            const id = document.getElementById('editEmailId').value;
            const content = document.getElementById('editEmailContent').value;

            axios.put(`/emails/${id}`, { content: content })
                .then(response => {
                    if (response.data.message === 'Email updated successfully') {
                        swal.fire({
                            title: 'Success',
                            text: 'Email updated successfully!',
                            icon: 'success'
                        }).then(() => {
                            closeEditModal();
                            fetchEmails();
                        });
                    } else {
                        swal.fire({
                            title: 'Error',
                            text: 'Failed to update email.',
                            icon: 'error'
                        });
                    }
                })
                .catch(error => {
                    swal.fire({
                        title: 'Error',
                        text: 'An error occurred while updating the email.',
                        icon: 'error'
                    });
                    console.error('Error:', error);
                });
        }
    
        function removeEmail(emailId) {
            swal.fire({
                title: 'Are you sure?',
                text: 'Do you really want to delete this email?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, keep it'
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.delete(`/emails/${emailId}`)
                        .then(response => {
                            swal.fire({
                                title: 'Email Deleted',
                                text: 'The email has been successfully deleted.',
                                icon: 'success'
                            });
                            fetchEmails();
                        })
                        .catch(error => {
                            swal.fire({
                                title: 'Error',
                                text: 'Failed to delete email. Please try again.',
                                icon: 'error'
                            });
                            console.error('Error:', error);
                        });
                }
            });
        }
    
        function toggleSavedEmails() {
            console.log('Toggle button clicked'); 
            const savedEmailsSection = document.getElementById('savedEmailsSection');
            const toggleButton = document.getElementById('toggleSavedEmails');

            if (savedEmailsSection.classList.contains('hidden')) {
                savedEmailsSection.classList.remove('hidden');
                toggleButton.innerHTML = '<i class="fas fa-save"></i> Hide Saved Emails';
                adjustContainerHeights();
            } else {
                savedEmailsSection.classList.add('hidden');
                toggleButton.innerHTML = '<i class="fas fa-save"></i> Show Saved Emails';
            }
        }
    
        function adjustContainerHeights() {
            const formContainer = document.querySelector('.form-container');
            const resultsContainer = document.querySelector('.results-container');
            const generatedEmail = document.getElementById('generatedEmail');
    
            formContainer.style.height = 'auto';
            resultsContainer.style.height = 'auto';
    
            const formHeight = formContainer.offsetHeight;
            const resultsHeight = resultsContainer.offsetHeight;
            const maxHeight = Math.max(formHeight, resultsHeight);
    
            formContainer.style.height = `${maxHeight}px`;
            resultsContainer.style.height = `${maxHeight}px`;
    
            const savedEmailsSection = document.getElementById('savedEmailsSection');
            if (!savedEmailsSection.classList.contains('hidden')) {
                savedEmailsSection.style.marginTop = `${maxHeight + 40}px`;
            }
        }
    
        document.addEventListener('DOMContentLoaded', adjustContainerHeights);
        window.addEventListener('resize', adjustContainerHeights);
    
        fetchEmails();
    </script>
</body>
</html>