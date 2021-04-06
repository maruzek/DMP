let addAdminInput = document.querySelector('.addProjectAdmin');
let project = document.querySelector('.project-id')
let projectID
if (project != null) {
  projectID = project.getAttribute('id')
}

if (addAdminInput !== null) {
  // Search for possible admins

  let autocopmleteArray = []

  $(".addProjectAdmin").autocomplete({
    source: autocopmleteArray
  });

  addAdminInput.addEventListener('input', (e) => {
    let input = e.target.value;

    if (input.trim() !== "") {
      $.ajax({
        type: "POST",
        url: "/admin/getPossibleAdmins",
        data: {
          input: input,
          project: projectID
        }
      }).then((response) => {
        if (response.length <= 0) {
          autocopmleteArray = []
        } else {
          for (let i = 0; i < response.length; i++) {
            if (!autocopmleteArray.includes(response[i].firstname + ' ' + response[i].lastname + ' (' + response[i].username + ', ' + response[i].class + ')')) {
              autocopmleteArray.push(response[i].firstname + ' ' + response[i].lastname + ' (' + response[i].username + ', ' + response[i].class + ')')
            }
          }
        }
      }).catch((error) => {
        console.error(error)
      })
    }
  })
}


// Delete Admin from project

const delButton = document.querySelectorAll('.deladmin-from-proj')
if (delButton != null) {

  for (let i = 0; i < delButton.length; i++) {
    const element = delButton[i];
    element.addEventListener('click', () => {
      let id = element.getAttribute('data-del-admin-id')
      id = parseInt(id)
      $.ajax({
        type: "POST",
        url: "/admin/delAdminFromProject",
        data: {
          data: id,
          project: projectID
        }
      }).then((response) => {
        if (response == "success") {
          $("#toast-deladmin-fromproj-success").toast('show')

          document.querySelector('#admin-' + id).remove()
        }
      })
    })
  }
}

// Change main admin 

const changeAdminBtn = document.querySelector('.change-mainadmin')

if (changeAdminBtn != null) {
  const changeAdminInput = document.querySelector('#change-mainadmin-input')

  changeAdminBtn.addEventListener('click', () => {
    $('#change-admin-panel').slideDown('slow')
  })

  let autocopmleteArrayChange = []
  $("#change-mainadmin-input").autocomplete({
    source: autocopmleteArrayChange
  })

  changeAdminInput.addEventListener('input', (e) => {
    const input = e.target.value

    if (input.trim() !== "") {
      $.ajax({
        type: "POST",
        url: "/admin/getPossibleAdmins",
        data: {
          input: input,
          project: projectID
        }
      }).then((response) => {
        if (response.length <= 0) {
          autocopmleteArray = []
        } else {
          for (let i = 0; i < response.length; i++) {
            if (!autocopmleteArrayChange.includes(response[i].firstname + ' ' + response[i].lastname + ' (' + response[i].username + ', ' + response[i].class + ')')) {
              autocopmleteArrayChange.push(response[i].firstname + ' ' + response[i].lastname + ' (' + response[i].username + ', ' + response[i].class + ')')
            }
          }
        }
      }).catch((error) => {
        console.error(error)
      })
    }
  })

  const changeAdminSubmit = document.querySelector('#change-mainadmin-btn')

  changeAdminSubmit.addEventListener('click', () => {
    const changeAdminValue = changeAdminInput.value;

    $.ajax({
      type: "POST",
      url: "/admin/changeMainAdmin",
      data: {
        input: changeAdminValue,
        project: projectID
      }
    }).then((response) => {
      location.reload();
    }).catch((error) => {
      console.error(error)
    })
  })
}


// Edit post

const editPostModalTrigger = document.querySelectorAll('.editPostModalTrigger')
const editPostModal = document.querySelector('#editPostModal')
if (editPostModal != null) {
  editPostModal.addEventListener('show.bs.modal', function (event) {
    // Button that triggered the modal
    let button = event.relatedTarget
    // Extract info from data-bs-* attributes
    let text = button.getAttribute('data-bs-editpost-text')
    let privatel = button.getAttribute('data-bs-editpost-private')
    let id = button.getAttribute('data-bs-editpost-id')
    // If necessary, you could initiate an AJAX request here
    // and then do the updating in a callback.
    //
    // Update the modal's content. var modalBodyInput = editPostModal.querySelector('.modal-body input')
    let modalBodyTextArea = editPostModal.querySelector('#message-text')
    let modalBodyCheck = editPostModal.querySelector('#privacyCheck')
    let submitBtn = editPostModal.querySelector('#editpost-submit')
    modalBodyTextArea.value = text
    submitBtn.setAttribute('data-editpost-submit-id', id)


    if (privatel == 1) {
      modalBodyCheck.setAttribute('checked', 'true')
    } else {
      modalBodyCheck.removeAttribute('checked')
    }

    editpostSubmit = editPostModal.querySelector('#editpost-submit')
    editpostSubmit.addEventListener('click', (event) => {
      if (event.target.getAttribute('data-editpost-submit-id') == id) {
        let changedText = modalBodyTextArea.value
        let changedPrivacy
        if (modalBodyCheck.checked) {
          changedPrivacy = 1
        } else {
          changedPrivacy = 0
        }

        data = [changedText, changedPrivacy, id]
        $.ajax({
          type: "POST",
          url: "/projekt/editPost",
          data: {
            data: data
          }
        }).then((response) => {
          if (response == "success") {
            let postText = document.querySelector('#postText-' + id)
            postText.innerHTML = changedText
            $("#toast-editpost-success").toast("show")
          } else if (response == "nochange") {
            $("#toast-editpost-nochange").toast("show")
          }
        })
      }
    })
  })
}

// Delete post

let deletePostTrigger = document.querySelectorAll('.post-card-trash-icon')

if (deletePostTrigger != null) {
  for (let i = 0; i < deletePostTrigger.length; i++) {
    const element = deletePostTrigger[i];
    element.addEventListener('click', () => {
      let id = element.getAttribute('data-deletepost-id')
      if (confirm('Opravdu chcete smazat tento příspěvek?')) {
        $.ajax({
          type: "POST",
          url: "/projekt/deletePost",
          data: {
            data: id
          }
        }).then((response) => {
          if (response == "success") {
            let deletedPost = document.querySelector('#post-' + id).parentElement.remove()
            $("#toast-delpost-success").toast("show")
          } else {}
        })
      }
    })
  }
}

// Delete Project 

const projDelBtn = document.querySelectorAll('.admin-project-delete')
const delProjTable = document.querySelector('#projects-tbody')
const deletedProjTable = document.querySelector('#deleted-projects-tbody')

if (projDelBtn != null) {
  for (let i = 0; i < projDelBtn.length; i++) {
    const element = projDelBtn[i];
    const id = element.getAttribute('data-delproj-id')
    element.addEventListener('click', () => {
      let confirmMsg
      if (element.parentElement.parentElement.parentElement.getAttribute('id') == 'deleted-projects-tbody') {
        confirmMsg = "Opravdu chcete obnovit tento projekt?"
      } else {
        confirmMsg = "Opravdu chcete smazat tento projekt?"
      }
      if (confirm(confirmMsg)) {
        $.ajax({
          type: "POST",
          url: "/admin/delProject",
          data: {
            data: id
          }
        }).then((response) => {
          if (response == "success") {
            const projectTr = document.querySelector('#project-' + id)
            const clone = projectTr.cloneNode(true)
            const delBtn = clone.childNodes[15].childNodes[1].innerHTML = 'Obnovit'
            deletedProjTable.insertBefore(clone, deletedProjTable.childNodes[0])
            projectTr.remove()
            $("#toast-delproj-success").toast("show")
          } else if (response == "recovered") {
            const projectTr = document.querySelector('#project-' + id)
            const clone = projectTr.cloneNode(true)
            const delBtn = clone.childNodes[15].childNodes[1].innerHTML = 'Smazat'
            delProjTable.insertBefore(clone, delProjTable.childNodes[0])
            projectTr.remove()
            $("#toast-delproj-success").toast("show")
          } else {}
        })
      }
    })
  }
}

// Follow

const followBtn = document.querySelector('#followBtn')

if (followBtn != null) {
  followBtn.addEventListener('click', () => {
    $.ajax({
      type: "POST",
      url: "/projekt/follow",
      data: {
        project: projectID
      }
    }).then((response) => {
      if (response == "followsuccess") {
        followBtn.classList.remove(['btn-outline-primary'])
        followBtn.classList.add(['btn-outline-secondary'])
        followBtn.textContent = "Přestat sledovat"

        $('#toast-follow-success').toast('show')

        const numberOfFollowsEl = document.querySelector('#number-of-follows')
        const numberOfFollows = parseInt(numberOfFollowsEl.textContent) + 1
        if (numberOfFollows > 0 && numberOfFollows < 5) {
          numberOfFollowsEl.innerHTML = ' ' + numberOfFollows + ' sledující'
        } else if (numberOfFollows > 4 || numberOfFollows == 0) {
          numberOfFollowsEl.innerHTML = ' ' + numberOfFollows + ' sledujících'
        }
      } else if (response == "unfollowsuccess") {
        followBtn.classList.remove(['btn-outline-secondary'])
        followBtn.classList.add(['btn-outline-primary'])
        followBtn.textContent = "Začít sledovat"

        $('#toast-unfollow-success').toast('show')

        const numberOfFollowsEl = document.querySelector('#number-of-follows')
        const numberOfFollows = parseInt(numberOfFollowsEl.textContent) - 1
        if (numberOfFollows > 0 && numberOfFollows < 5) {
          numberOfFollowsEl.innerHTML = ' ' + numberOfFollows + ' sledující'
        } else if (numberOfFollows > 4 || numberOfFollows == 0) {
          numberOfFollowsEl.innerHTML = ' ' + numberOfFollows + ' sledujících'
        }
      } else if (response == "followfail" || response == "unfollowfail") {
        $('#toast-follow-fail').toast('show')
      }
    })
  })
}


// Member

const memberBtn = document.querySelector('#memberBtn')

if (memberBtn != null) {
  memberBtn.addEventListener('click', () => {
    $.ajax({
      type: "POST",
      url: "/projekt/member",
      data: {
        project: projectID
      }
    }).then((response) => {
      if (response == "membersuccess") {
        memberBtn.classList.remove(['btn-primary'])
        memberBtn.classList.add(['btn-secondary'])
        memberBtn.textContent = "Zrušit členství"

        $('#toast-member-success').toast('show')

        const numberOfMembersEl = document.querySelector('#number-of-members')
        const numberOfMembers = parseInt(numberOfMembersEl.textContent) + 1
        if (numberOfMembers > 1 && numberOfMembers < 5) {
          numberOfMembersEl.innerHTML = ' ' + numberOfMembers + ' členové'
        } else if (numberOfMembers > 4 || numberOfMembers == 0) {
          numberOfMembersEl.innerHTML = ' ' + numberOfMembers + ' členů'
        } else if (numberOfMembers == 1) {
          numberOfMembersEl.innerHTML = ' ' + numberOfMembers + ' člen'
        }
      } else if (response == "unmembersuccess") {
        memberBtn.classList.remove(['btn-secondary'])
        memberBtn.classList.add(['btn-primary'])
        memberBtn.textContent = "Stát se členem"

        $('#toast-unmember-success').toast('show')

        const numberOfMembersEl = document.querySelector('#number-of-members')
        const numberOfMembers = parseInt(numberOfMembersEl.textContent) - 1
        if (numberOfMembers > 0 && numberOfMembers < 5) {
          numberOfMembersEl.innerHTML = ' ' + numberOfMembers + ' členové'
        } else if (numberOfMembers > 4 || numberOfMembers == 0) {
          numberOfMembersEl.innerHTML = ' ' + numberOfMembers + ' členů'
        } else if (numberOfMembers == 1) {
          numberOfMembersEl.innerHTML = ' ' + numberOfMembers + ' člen'
        }
      } else if (response == "memberfail" || response == "unmemberfail") {
        $('#toast-member-fail').toast('show')
      }
    })
  })
}

// Accept Member

const acceptMemberBtn = document.querySelectorAll('.acceptMemberBtn')
if (acceptMemberBtn != null) {
  for (let i = 0; i < acceptMemberBtn.length; i++) {
    const element = acceptMemberBtn[i];
    element.addEventListener('click', () => {
      const memberID = element.getAttribute('data-member-id')
      $.ajax({
        type: "POST",
        url: "/projekt/acceptMember",
        data: {
          project: projectID,
          member: memberID,
          type: "accept"
        }
      }).then((response) => {
        const tr = document.querySelector('#member-request-tr')

        if (response == "accept-success") {
          $('#toast-memberaccept-success').toast('show')
          tr.remove()
        } else if (response == "accept-fail") {
          $('#toast-memberrequest-fail').toast('show')
        }
      })
    })
  }
}

// Decline Member

const declineMemberBtn = document.querySelectorAll('.declineMemberBtn')
if (declineMemberBtn != null) {
  for (let i = 0; i < declineMemberBtn.length; i++) {
    const element = declineMemberBtn[i];
    element.addEventListener('click', () => {
      const memberID = element.getAttribute('data-member-id')
      $.ajax({
        type: "POST",
        url: "/projekt/acceptMember",
        data: {
          project: projectID,
          member: memberID,
          type: "decline"
        }
      }).then((response) => {
        const tr = document.querySelector('#member-request-tr')

        if (response == "decline-success") {
          $('#toast-memberdecline-success').toast('show')
          tr.remove()
        } else if (response == "decline-fail") {
          $('#toast-memberrequest-fail').toast('show')
        }
      })
    })
  }
}


// Search for user in Admin

const searchUserInput = document.querySelector('#searchUserInput')

if (searchUserInput != null) {
  searchUserInput.addEventListener('input', (e) => {
    let input = e.target.value

    let trTest = document.querySelectorAll('.testlist')
    if (trTest != null) {
      for (let i = 0; i < trTest.length; i++) {
        const element = trTest[i];
        element.remove()
      }
    }

    if (input.trim() !== "") {
      $.ajax({
        type: "POST",
        url: "/admin/searchAllUsers",
        data: {
          input: input
        }
      }).then((response) => {
        if (response.length <= 0) {
          autocopmleteArray = []
        } else {
          for (let i = 0; i < response.length; i++) {
            let tr = document.createElement("TR")
            let username = document.createElement('TD')
            username.innerHTML = response[i].username
            let id = document.createElement('TD')
            id.innerHTML = response[i].id
            let name = document.createElement('TD')
            name.innerHTML = response[i].firstname + ' ' + response[i].lastname
            let userClass = document.createElement('TD')
            userClass.innerHTML = response[i].class
            let tag = document.createElement('TD')
            tag.innerHTML = response[i].tag
            let firstLogin = document.createElement('TD')
            if (response[i].firstLogin != null) {
              const options = {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: 'numeric',
                minute: 'numeric'
              }
              let firstLoginDate = new Date(response[i].firstLogin.timestamp * 1000).toLocaleDateString('cs-cs', options)
              firstLogin.innerHTML = firstLoginDate
            }
            let viewBtn = document.createElement('TD')
            viewBtn.innerHTML = '<a class="btn btn-success" href="/user/' + response[i].username + '" target="_blank">Zobrazit</a>'
            let editBtn = document.createElement('TD')
            editBtn.innerHTML = '<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editUserModal" data-bs-userid="' + response[i].id + '">Spravovat</button>'

            let delBtn = document.createElement('TD')
            delBtn.innerHTML = '<button type="button" class="btn btn-danger"  data-bs-toggle="modal" data-bs-target="#modalDelUser" data-bs-userid="' + response[i].id + '">Smazat</button>'

            tr.appendChild(id)
            tr.appendChild(name)
            tr.appendChild(userClass)
            tr.appendChild(username)
            tr.appendChild(tag)
            tr.appendChild(firstLogin)
            tr.appendChild(viewBtn)
            tr.appendChild(editBtn)
            tr.appendChild(delBtn)


            tr.classList.add('testlist')
            document.querySelector('#allUsers-tbody').appendChild(tr)
          }
        }
      }).catch((error) => {
        console.error(error)
      })
    }
  })
}

// Delete user

const delUserModal = document.getElementById('modalDelUser')
if (delUserModal != null) {
  delUserModal.addEventListener('show.bs.modal', function (event) {
    // Button that triggered the modal
    const button = event.relatedTarget
    // Extract info from data-bs-* attributes
    const id = button.getAttribute('data-bs-userid')
    // If necessary, you could initiate an AJAX request here
    // and then do the updating in a callback.
    //
    // Update the modal's content.
    const confirmBtn = delUserModal.querySelector('.admin-deluser-confirm')

    confirmBtn.addEventListener('click', () => {
      $.ajax({
        type: "POST",
        url: "/admin/delUser",
        data: {
          id: id
        }
      }).then((response) => {

        if (response == "success") {
          $('#toast-deluser-success').toast('show')

          document.querySelector('#searchUserInput').value = ""
          document.querySelector('#allUsers-tbody').textContent = ""
        } else if (response == "dbfail") {
          $('#toast-deluser-dbfail').toast('show')
        }


      }).catch((error) => {
        console.error(error)
      })
    })
  })
}

// Edit user as Admin 

const editUserModal = document.getElementById('editUserModal')
if (editUserModal != null) {
  editUserModal.addEventListener('show.bs.modal', function (event) {
    // Button that triggered the modal
    const button = event.relatedTarget
    // Extract info from data-bs-* attributes
    const id = button.getAttribute('data-bs-userid')
    // If necessary, you could initiate an AJAX request here
    // and then do the updating in a callback.
    //
    // Update the modal's content.
    const delimgBtn = editUserModal.querySelector('.admin-deluser-img')
    const deldescBtn = editUserModal.querySelector('.admin-deluser-desc')

    const editUserPostsTable = editUserModal.querySelector('#deletedPostsTable-tbody')
    $.ajax({
      type: "POST",
      url: "/admin/getUserDeletedPosts",
      data: {
        id: id
      }
    }).then((response) => {

      for (let i = 0; i < response.length; i++) {
        const options = {
          year: 'numeric',
          month: 'long',
          day: 'numeric',
          hour: 'numeric',
          minute: 'numeric'
        }

        const post = response[i];

        let tr = document.createElement("TR")

        let id = document.createElement('TD')
        id.innerHTML = response[i].id
        let text = document.createElement('TD')
        text.innerHTML = response[i].text
        let project = document.createElement('TD')
        project.innerHTML = response[i].project

        let posted = document.createElement('TD')
        posted.innerHTML = new Date(response[i].posted.timestamp * 1000).toLocaleDateString('cs-cs', options)
        let privacy = document.createElement('TD')
        if (privacy == 1) {
          privacy.innerHTML = 'ANO'
        } else {
          privacy.innerHTML = 'NE'
        }

        let restoreBtn = document.createElement('TD')
        restoreBtn.innerHTML = '<button class="btn btn-danger delete-user-post" data-delete-user-post="' + post.id + '">Obnovit</button>'

        tr.appendChild(id)
        tr.appendChild(text)
        tr.appendChild(project)
        tr.appendChild(privacy)
        tr.appendChild(posted)
        tr.appendChild(restoreBtn)

        tr.setAttribute('id', 'restorePostTr-' + post.id)

        editUserPostsTable.appendChild(tr)

        let deleteUserPostBtns = document.querySelectorAll('.delete-user-post')
        for (let k = 0; k < deleteUserPostBtns.length; k++) {
          const element = deleteUserPostBtns[k];

          element.addEventListener('click', () => {
            const postId = element.getAttribute('data-delete-user-post')

            $.ajax({
              type: "POST",
              url: "/admin/restorePost",
              data: {
                id: postId
              }
            }).then((response) => {
              if (response == "success") {
                $('#toast-delUserPost-success').toast('show')
                document.querySelector('#restorePostTr-' + post.id).remove()
              } else if (response == "dbfail") {
                $('#toast-delUserPost-dbfail').toast('show')
              }
            }).catch((error) => {
              console.error(error)
            })
          })
        }
      }


    }).catch((error) => {
      console.error(error)
    })


    delimgBtn.addEventListener('click', () => {
      $.ajax({
        type: "POST",
        url: "/admin/editUser",
        data: {
          id: id,
          type: "delimg"
        }
      }).then((response) => {

        if (response == "success") {
          $('#toast-edituser-delimg-success').toast('show')
        } else if (response == "alreadydefault") {
          $('#toast-edituser-delimg-alreadydefault').toast('show')
        } else if (response == "badrequest") {
          $('#toast-edituser-fail').toast('show')
        }


      }).catch((error) => {
        console.error(error)
      })
    })

    deldescBtn.addEventListener('click', () => {
      $.ajax({
        type: "POST",
        url: "/admin/editUser",
        data: {
          id: id,
          type: "deldesc"
        }
      }).then((response) => {
        if (response == "success") {
          $('#toast-edituser-deldesc-success').toast('show')
        } else if (response == "alreadyclear") {
          $('#toast-edituser-deldesc-alreadyclear').toast('show')
        } else if (response == "badrequest") {
          $('#toast-edituser-fail').toast('show')
        }

      }).catch((error) => {
        console.error(error)
      })
    })

  })
}

// Admin new block

const newBlockBtn = document.querySelector('.newblockBtn')

if (newBlockBtn != null) {
  newBlockBtn.addEventListener('click', () => {
    $('.newblockSettings').slideDown('slow')
  })

  const newblockProject = document.querySelector('#newblockProject')
  const newblockSubmit = document.querySelector('#newblockSubmit')

  newblockSubmit.addEventListener('click', () => {
    let newblockProjectValue = newblockProject.value
    $.ajax({
      type: "POST",
      url: "/admin/addNewBlock",
      data: {
        id: newblockProjectValue,
        type: "project"
      }
    }).then((response) => {

      if (response == "success") {
        $('.newblockSettings').slideUp('slow')
        $('#toast-newIndexBlock-success').toast('show')
      }
    }).catch((error) => {
      console.error(error)
    })
  })
}

// Delete member from project

let delMemberBtn = document.querySelectorAll('.delete-member')

if (delMemberBtn != null) {
  for (let i = 0; i < delMemberBtn.length; i++) {
    const element = delMemberBtn[i];

    element.addEventListener('click', () => {
      const id = element.getAttribute('id')
      const userid = id.split('-')[2]
      const projid = id.split('-')[4]

      $.ajax({
        type: "POST",
        url: "/projekt/deleteMember",
        data: {
          member: userid,
          project: projid
        }
      }).then((response) => {
        if (response == "success") {
          $('#toast-memberdel-success').toast('show');

          element.parentElement.parentElement.remove()
        } else if (response == "nonadmin" || response == "dbfail") {
          $('#toast-memberdel-fail').toast('show');
        }

      }).catch((error) => {
        console.error(error)
      })
    })
  }
}

// Delete Hero image

const delherobtn = document.querySelectorAll('.delhero')

if (delherobtn != null) {
  for (let i = 0; i < delherobtn.length; i++) {
    const element = delherobtn[i];

    element.addEventListener('click', () => {
      const id = element.getAttribute('id').split('-')[1]

      $.ajax({
        type: "POST",
        url: "/projekt/deleteHero",
        data: {
          id: id,
          project: projectID
        }
      }).then((response) => {

        element.parentElement.parentElement.remove()
      }).catch((error) => {
        console.error(error)
      })
    })
  }
}

// Post seens

let seensModal = document.getElementById('seensModal')

if (seensModal != null) {
  seensModal.addEventListener('show.bs.modal', function (event) {
    // Button that triggered the modal
    const button = event.relatedTarget
    // Extract info from data-bs-* attributes
    const postid = button.getAttribute('data-bs-postid')
    // If necessary, you could initiate an AJAX request here
    // and then do the updating in a callback.
    //

    const tbody = seensModal.querySelector('#seens-modal-tbody')
    tbody.innerHTML = ''

    $.ajax({
      type: "POST",
      url: "/projekt/getPostSeens",
      data: {
        id: postid
      }
    }).then((response) => {
      for (let i = 0; i < response.length; i++) {
        const tr = document.createElement("TR")
        const element = response[i]
        let name = document.createElement('TD')
        name.innerHTML = element
        tr.appendChild(name)

        tbody.appendChild(tr)
      }

    }).catch((error) => {
      console.error(error)
    })
  })
}

// new event

const newEventBtn = document.querySelector('.newEventBtn')

if (newEventBtn != null) {
  newEventBtn.addEventListener('click', () => {
    $('.newEventSettings').slideDown('slow')
  })

  const newEventStart = document.querySelector('#new_event_start')
  const newEventEnd = document.querySelector('#new_event_end')
  const newEventSubmit = document.querySelector('#new_event_submit')

  const newEventForm = document.querySelector('#newEventForm')
  const newEventEndBeforeStart = document.querySelector('#newEventEndBeforeStart')

  newEventEnd.addEventListener('change', () => {
    if (newEventEnd.value < newEventStart.value) {
      if (newEventEndBeforeStart.classList.contains('d-none')) {
        newEventEndBeforeStart.classList.remove('d-none')
        newEventEndBeforeStart.classList.add('d-block')
        newEventSubmit.classList.add('disabled')
      }

    } else if (newEventEnd.value > newEventStart.value && newEventEndBeforeStart.classList.contains('d-block')) {
      newEventEndBeforeStart.classList.remove('d-block')
      newEventEndBeforeStart.classList.add('d-none')
      newEventSubmit.classList.remove('disabled')
    }
  })

  newEventStart.addEventListener('change', () => {
    if (newEventEnd.value < newEventStart.value && newEventEnd.value != null) {
      if (newEventEndBeforeStart.classList.contains('d-none')) {
        newEventEndBeforeStart.classList.remove('d-none')
        newEventEndBeforeStart.classList.add('d-block')
        newEventSubmit.classList.add('disabled')
      }

    } else if (newEventEnd.value > newEventStart.value && newEventEndBeforeStart.classList.contains('d-block')) {
      newEventEndBeforeStart.classList.remove('d-block')
      newEventEndBeforeStart.classList.add('d-none')
      newEventSubmit.classList.remove('disabled')
    }
  })
}

// Remove index block

const removeIbBtn = document.querySelectorAll('.removeIndexBlock')

if (removeIbBtn != null) {
  for (let i = 0; i < removeIbBtn.length; i++) {
    const element = removeIbBtn[i];
    const id = element.getAttribute('id').split('-')[1]

    element.addEventListener('click', () => {
      $.ajax({
        type: "POST",
        url: "/admin/deleteIndexBlock",
        data: {
          id: id
        }
      }).then((response) => {

        if (response == "success") {
          $('#toast-delIB-success').toast('show')
        } else if (response == "dberror") {
          $('#toast-delIB-dberror').toast('show')
        } else if (response == "nonexistant") {
          $('#toast-delIB-nonexistant').toast('show')
        }

      }).catch((error) => {
        console.error(error)
      })
    });
  }
}

// DelAbsAdmin

const delAbsAdminBtns = document.querySelectorAll('.delAbsAdmin')

if (delAbsAdminBtns != null) {
  for (let i = 0; i < delAbsAdminBtns.length; i++) {
    const element = delAbsAdminBtns[i];

    element.addEventListener('click', () => {
      if (confirm("Opravdu chcete tohoto administrátora odstranit?")) {
        const id = element.getAttribute('data-admin-id')

        $.ajax({
          type: "POST",
          url: "/admin/deleteAbsAdmin",
          data: {
            id: id
          }
        }).then((response) => {

          if (response == "success") {
            $('#toast-delAbsAdmin-success').toast('show')
            document.querySelector('#delAbsAdmin-tr-' + id).remove()
          } else if (response == "dbfail") {
            $('#toast-delAbsAdmin-dbfail').toast('show')
          } else if (response == "toofewadmins") {
            $('#toast-delAbsAdmin-toofewadmins').toast('show')
          }

        }).catch((error) => {
          console.error(error)
        })
      }
    })
  }
}

// Edit event

const eventSettingsModal = document.querySelector("#eventSettingsModal")

if (eventSettingsModal != null) {

  eventSettingsModal.addEventListener('show.bs.modal', function (event) {
    // Button that triggered the modal
    let button = event.relatedTarget
    // Extract info from data-bs-* attributes
    let name = button.getAttribute('data-bs-editevent-name')
    let privacy = button.getAttribute('data-bs-editevent-privacy')
    let location = button.getAttribute('data-bs-editevent-location')
    let start = button.getAttribute('data-bs-editevent-start')
    let end = button.getAttribute('data-bs-editevent-end')
    let description = button.getAttribute('data-bs-editevent-description')
    const id = button.getAttribute('data-bs-editevent-id')

    // If necessary, you could initiate an AJAX request here
    // and then do the updating in a callback.
    //
    // Update the modal's content. var modalBodyInput = editPostModal.querySelector('.modal-body input')
    let nameForm = eventSettingsModal.querySelector('#editevent-name')
    let privacyForm = eventSettingsModal.querySelector('#editevent-privacy')
    let locationForm = eventSettingsModal.querySelector('#editevent-location')
    let startForm = eventSettingsModal.querySelector('#editevent-start')
    let endForm = eventSettingsModal.querySelector('#editevent-end')
    let descriptionForm = eventSettingsModal.querySelector('#editevent-description')
    nameForm.value = name
    locationForm.value = location
    startForm.value = start
    endForm.value = end
    descriptionForm.value = description

    if (privacy == 1) {
      privacyForm.setAttribute('checked', 'true')
    } else {
      privacyForm.removeAttribute('checked')
    }

    console.log(privacyForm.checked)

    const editEventBtn = eventSettingsModal.querySelector('#editevent-submit')

    editEventBtn.addEventListener('click', (event) => {
      $.ajax({
        type: "POST",
        url: "/projekt/editEvent",
        data: {
          id: id,
          name: nameForm.value,
          location: locationForm.value,
          start: startForm.value,
          end: endForm.value,
          description: descriptionForm.value,
          privacy: privacyForm.checked
        }
      }).then((response) => {
        if (response == "success") {
          $("#toast-editevent-success").toast("show")

          document.querySelector('#event-' + id + '-name').textContent = nameForm.value
          document.querySelector('#event-' + id + '-location').innerHTML = '<i class="fas fa-map-marker"></i> ' + locationForm.value
          const options = {
            year: 'numeric',
            month: 'numeric',
            day: 'numeric',
            hour: 'numeric',
            minute: 'numeric'
          }
          const newdateStart = new Date(startForm.value)
          const newdateEnd = new Date(endForm.value)
          document.querySelector('#event-' + id + '-date').innerHTML = newdateStart.toLocaleDateString('cs-cs', options) + ' až ' + newdateEnd.toLocaleDateString('cs-cs', options)
          document.querySelector('#event-' + id + '-description').innerHTML = descriptionForm.value
        } else if (response['result'] == "nochange") {
          $("#toast-editevent-nochange").toast("show")
        }
      })

    })
  })

}


// Delete event

const delEventBtns = document.querySelectorAll('.event-delete-btn')

if (delEventBtns != null) {
  for (let i = 0; i < delEventBtns.length; i++) {
    const element = delEventBtns[i];

    element.addEventListener('click', () => {
      if (confirm('Opravdu chcete tuto událost smazat?')) {
        const id = element.getAttribute('data-delevent-id')

        $.ajax({
          type: "POST",
          url: "/projekt/deleteEvent",
          data: {
            id: id
          }
        }).then((response) => {
          if (response == "success") {
            $("#toast-editevent-success").toast("show")

            document.querySelector('#event-card-' + id).parentElement.remove()
          } else if (response['result'] == "nochange") {
            $("#toast-editevent-nochange").toast("show")
          }
        })
      }
    })
  }
}

// Media modal

const mediaModal = document.getElementById('mediaModal')
if (mediaModal != null) {
  mediaModal.addEventListener('show.bs.modal', function (event) {
    // Button that triggered the modal
    var button = event.relatedTarget
    // Extract info from data-bs-* attributes
    var img = button.getAttribute('src')
    // If necessary, you could initiate an AJAX request here
    // and then do the updating in a callback.
    //
    // Update the modal's content.
    mediaModal.querySelector('#media-modal-img').setAttribute('src', img)

  })
}

// Fotorama

let post = document.querySelector('.project-post-wrapper')
if (post != null) {
  let postWidth = post.offsetWidth
  const fotorama = document.querySelectorAll('.fotorama')
  for (let i = 0; i < fotorama.length; i++) {
    const element = fotorama[i];
    element.setAttribute('data-minwidth', (postWidth * 80) / 100)
  }
}

// Mazání Všeho obsahu

const delAllBtn = document.querySelector('#mainDelBtn')

if (delAllBtn != null) {

  delAllBtn.addEventListener('click', () => {
    if (confirm('Tato akce již nepůjde navrátit a obsah se smaže z databáze. Chcete pokračovat?')) {
      $.ajax({
        type: "POST",
        url: "/admin/deleteAllContent"
      }).then((response) => {
        if (response == "success") {
          $("#toast-delAllContent-success").toast("show")
        } else if (response == "dbfail") {
          $("#toast-delAllContent-dbfail").toast("show")
        }
      })
    }
  })
}