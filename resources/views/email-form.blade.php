<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Callbox Email Generator</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/css/style.css">
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
                            <input type="text" id="subject" name="subject" class="form-control" placeholder="Type here..." required>
                        </div>
                        <div class="form-group">
                            <label for="keywords">Enter Keywords (Optional):</label>
                            <input type="text" id="keywords" name="keywords" class="form-control" placeholder="Type here...">
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
    
        <!-- Show Saved Emails Button -->
        <div class="text-center mt-4">
            <button id="toggleSavedEmails" class="btn btn-success" onclick="toggleSavedEmails()"><i class="fas fa-save"></i> Show Saved Emails</button>
        </div>
    
        <!-- Saved Emails Section -->
        <div id="savedEmailsSection" class="card saved-emails-section hidden mt-4">
            <div class="card-body">
                <h3>Saved Emails</h3>
                <ul id="savedEmails" class="list-group">
                    @foreach ($emails as $email)
                        <li class="list-group-item">
                            <h4>{{ $email->subject }}</h4>
                            <p id="emailContentPreview{{ $email->id }}">{{ implode(' ', array_slice(explode(' ', $email->content), 0, 30)) }}...</p>
                            <p id="emailContentFull{{ $email->id }}" class="hidden" style="white-space: pre-wrap;">{{ $email->content }}</p>
                            <button class="btn btn-warning edit-button" onclick="editEmail('{{ $email->id }}', '{{ $email->subject }}', `{{ $email->content }}`)">Edit</button>
                            <button class="btn btn-info read-more-button" onclick="toggleFullEmail('{{ $email->id }}')">Read More</button>
                            <button class="btn btn-danger remove-button" onclick="removeEmail('{{ $email->id }}')">Remove</button>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.getElementById('emailForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent the form from submitting normally
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
                    adjustContainerHeights(); // Adjust container heights after generating email
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
                        let preview = email.content.split(' ').slice(0, 30).join(' ') + '...';
                        listItem.innerHTML = `
                            <h4>${email.subject}</h4>
                            <p id="emailContentPreview${email.id}">${preview}</p>
                            <p id="emailContentFull${email.id}" class="hidden" style="white-space: pre-wrap;">${email.content}</p>
                            <button class="btn btn-warning edit-button" onclick="editEmail('${email.id}', '${email.subject}', \`${email.content.replace(/'/g, "\\'")}\`)">Edit</button>
                            <button class="btn btn-info read-more-button" onclick="toggleFullEmail('${email.id}')">Read More</button>
                            <button class="btn btn-danger remove-button" onclick="removeEmail('${email.id}')">Remove</button>
                        `;
                        emailList.appendChild(listItem);
                    });
                })
                .catch(error => console.error('Error:', error));
        }
    
        function toggleFullEmail(emailId) {
            const previewElement = document.getElementById(`emailContentPreview${emailId}`);
            const fullElement = document.getElementById(`emailContentFull${emailId}`);
            if (previewElement.classList.contains('hidden')) {
                previewElement.classList.remove('hidden');
                fullElement.classList.add('hidden');
            } else {
                previewElement.classList.add('hidden');
                fullElement.classList.remove('hidden');
            }
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
    
            fetch(`/emails/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ content })
            })
            .then(response => response.json())
            .then(data => {
                if (data.message === 'Email updated successfully') {
                    location.reload();
                } else {
                    alert('Failed to update email');
                }
            })
            .catch(error => console.error('Error:', error));
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