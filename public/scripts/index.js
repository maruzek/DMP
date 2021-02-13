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
        console.log(response)
        if (response.length <= 0) {
          autocopmleteArray = []
        } else {
          for (let i = 0; i < response.length; i++) {
            if (!autocopmleteArray.includes(response[i].firstname + ' ' + response[i].lastname + ' (' + response[i].username + ', ' + response[i].class + ')')) {
              autocopmleteArray.push(response[i].firstname + ' ' + response[i].lastname + ' (' + response[i].username + ', ' + response[i].class + ')')
            }
          }
        }
        console.log(autocopmleteArray)
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
        console.log(response)
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
        console.log(response)
        if (response.length <= 0) {
          autocopmleteArray = []
        } else {
          for (let i = 0; i < response.length; i++) {
            if (!autocopmleteArrayChange.includes(response[i].firstname + ' ' + response[i].lastname + ' (' + response[i].username + ', ' + response[i].class + ')')) {
              autocopmleteArrayChange.push(response[i].firstname + ' ' + response[i].lastname + ' (' + response[i].username + ', ' + response[i].class + ')')
            }
          }
        }
        console.log(autocopmleteArrayChange)
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
      console.log(response)
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
    console.log(text)
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
      console.log('je true')
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
          console.log(response)
          if (response == "success") {
            let deletedPost = document.querySelector('#post-' + id).remove()
            $("#toast-delpost-success").toast("show")
          } else {
            console.log('chyba')
          }
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
      console.log(element.parentElement.parentElement.parentElement)
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
          } else {
            console.log('chyba')
          }
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
        console.log(response)
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
        } else if(numberOfMembers == 1) {
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
        }else if(numberOfMembers == 1) {
          numberOfMembersEl.innerHTML = ' ' + numberOfMembers + ' člen'
        }
      } else if (response == "memberfail" || response == "unmemberfail") {
        $('#toast-member-fail').toast('show')
        console.log(response)
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
        console.log(response)
        const tr = document.querySelector('#member-request-tr')

        if(response == "accept-success") {
          $('#toast-memberaccept-success').toast('show')
          tr.remove()
        }  else if(response == "accept-fail") {
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
        console.log(response)
        const tr = document.querySelector('#member-request-tr')

        if(response == "decline-success") {
          $('#toast-memberdecline-success').toast('show')
          tr.remove()
        }  else if(response == "decline-fail") {
          $('#toast-memberrequest-fail').toast('show')
        }
      })
    })
  }
}


// Search for user in Admin

const searchUserInput = document.querySelector('#searchUserInput')

if(searchUserInput != null) {
  searchUserInput.addEventListener('input', (e)=>{
    let input = e.target.value

    let trTest = document.querySelectorAll('.testlist')
    if(trTest != null) {
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
        console.log(response)
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
            let viewBtn = document.createElement('TD')
            viewBtn.innerHTML = '<a class="btn btn-success" href="/user/'+ response[i].username +'" target="_blank">Zobrazit</a>'
            let editBtn = document.createElement('TD')
            editBtn.innerHTML = '<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editUserModal" data-bs-userid="'+ response[i].id +'">Spravovat</button>'

            let delBtn = document.createElement('TD')
            delBtn.innerHTML = '<a class="btn btn-danger" id="admin-deluser">Smazat</a>'

            tr.appendChild(id)
            tr.appendChild(name)
            tr.appendChild(userClass)
            tr.appendChild(username)
            tr.appendChild(tag)
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

// Edit user as Admin 

const editUserModal = document.getElementById('editUserModal')
if(editUserModal != null) {
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
  
    delimgBtn.addEventListener('click', ()=>{
      $.ajax({
        type: "POST",
        url: "/admin/editUser",
        data: {
          id: id,
          type: "delimg"
        }
      }).then((response) => {
        console.log(response)
  
        if(response == "success") {
          $('#toast-edituser-delimg-success').toast('show')
        } else if(response == "alreadydefault") {
          $('#toast-edituser-delimg-alreadydefault').toast('show')
        } else if(response == "badrequest") {
          $('#toast-edituser-fail').toast('show')
        } 
  
        
      }).catch((error) => {
        console.error(error)
      })
    })
  
    deldescBtn.addEventListener('click', ()=>{
      $.ajax({
        type: "POST",
        url: "/admin/editUser",
        data: {
          id: id,
          type: "deldesc"
        }
      }).then((response) => {
        console.log(response)
        if(response == "success") {
          $('#toast-edituser-deldesc-success').toast('show')
        } else if(response == "alreadyclear") {
          $('#toast-edituser-deldesc-alreadyclear').toast('show')
        } else if(response == "badrequest") {
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

if(newBlockBtn != null) {
  newBlockBtn.addEventListener('click', ()=> {
    $('.newblockSettings').slideDown('slow')
  })

  const newBlockType = document.querySelector('#newblockType')
  const newblockProject = document.querySelector('#newblockProject')
  const newblockPost= document.querySelector('#newblockPostInput')
  const newblockSubmit = document.querySelector('#newblockSubmit')

  newBlockType.addEventListener('change', (e) => {
    if(e.target.value == "project") {
      if(newblockPost.style.display != "none") {
      $('#newblockPost').slideUp('slow')
      }
      $('#newblockProject').slideDown('slow')
      $('#newblockSubmit').slideDown('slow')
    } else if (e.target.value == "post") {
      if(newblockProject.style.display != "none") {
        $('#newblockProject').slideUp('slow')
        }
      $('#newblockPost').slideDown('slow')
      $('#newblockSubmit').slideDown('slow')
    }

    newblockSubmit.addEventListener('click', () => {
      let newblockPostValue = newblockPost.value
      let newblockProjectValue = newblockProject.value
      let newBlockTypeValue = newBlockType.value
      console.log(newblockPostValue)
      if(newBlockTypeValue == "project") {
        $.ajax({
          type: "POST",
          url: "/admin/addNewBlock",
          data: {
            id: newblockProjectValue,
            type: "project"
          }
        }).then((response) => {
          console.log(response)    
          
          if(response == "success") {
            $('.newblockSettings').slideUp('slow')
          }
        }).catch((error) => {
          console.error(error)
        })
      } else if (newBlockTypeValue == "post") {
        $.ajax({
          type: "POST",
          url: "/admin/addNewBlock",
          data: {
            id: newblockPostValue,
            type: "post"
          }
        }).then((response) => {
          console.log(response)    
          
          if(response == "success") {
            $('.newblockSettings').slideUp('slow')
          }
        }).catch((error) => {
          console.error(error)
        })
      }
    })
  })
}

// Delete member from project

let delMemberBtn = document.querySelectorAll('.delete-member')

if(delMemberBtn != null) {
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
        console.log(response)    
        

      }).catch((error) => {
        console.error(error)
      })
    })
  }
}