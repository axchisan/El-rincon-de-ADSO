document.addEventListener("DOMContentLoaded", () => {
  // Formulario de comentarios
  const formComentario = document.getElementById("form-comentario")
  const comentarioStatus = document.getElementById("comentario-status")
  const commentsContainer = document.getElementById("comments-container")

  if (formComentario) {
    formComentario.addEventListener("submit", function (e) {
      e.preventDefault()

      const documentoId = this.getAttribute("data-documento-id")
      const comentarioTextarea = this.querySelector('textarea[name="comentario"]')
      const comentario = comentarioTextarea.value
      const btnEnviar = this.querySelector("#btn-enviar-comentario")
      const usuarioId = this.getAttribute("data-usuario-id")
      const nombreUsuario = this.getAttribute("data-nombre-usuario")

      if (!comentario.trim()) {
        alert("El comentario no puede estar vacío.")
        return
      }

      // Mostrar estado de carga
      comentarioStatus.style.display = "block"
      btnEnviar.disabled = true

      fetch("../../backend/gestionRecursos/add_comment.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `documento_id=${documentoId}&comentario=${encodeURIComponent(comentario)}`,
      })
        .then((response) => response.json())
        .then((data) => {
          // Ocultar estado de carga
          comentarioStatus.style.display = "none"
          btnEnviar.disabled = false

          if (data.success) {
            // Limpiar el textarea
            comentarioTextarea.value = ""

            // Añadir el comentario al DOM sin recargar la página
            const fechaActual = new Date().toLocaleString("es-ES", {
              day: "2-digit",
              month: "2-digit",
              year: "numeric",
              hour: "2-digit",
              minute: "2-digit",
            })

            // Eliminar mensaje de "no hay comentarios" si existe
            const noComments = commentsContainer.querySelector(".no-comments")
            if (noComments) {
              noComments.remove()
            }

            // Crear el nuevo comentario
            const nuevoComentario = document.createElement("div")
            nuevoComentario.className = "comment"
            nuevoComentario.innerHTML = `
              <div class="comment__content">
                <div class="comment__header">
                  <span class="comment__author">${nombreUsuario}</span>
                  <span class="comment__date">${fechaActual}</span>
                </div>
                <div class="comment__text">
                  <p>${comentario.replace(/\n/g, "<br>")}</p>
                </div>
                <div class="comment__actions">
                  <button class="btn-edit-comment" data-id="${data.comentario_id}" data-content="${encodeURIComponent(comentario)}">
                    <i class="fas fa-edit"></i> Editar
                  </button>
                  <button class="btn-delete-comment" data-id="${data.comentario_id}">
                    <i class="fas fa-trash-alt"></i> Eliminar
                  </button>
                </div>
              </div>
            `

            // Insertar el nuevo comentario al principio de la lista
            commentsContainer.insertBefore(nuevoComentario, commentsContainer.firstChild)

            // Añadir event listeners al nuevo comentario
            const editBtn = nuevoComentario.querySelector(".btn-edit-comment")
            const deleteBtn = nuevoComentario.querySelector(".btn-delete-comment")

            if (editBtn) {
              editBtn.addEventListener("click", handleEditComment)
            }

            if (deleteBtn) {
              deleteBtn.addEventListener("click", handleDeleteComment)
            }
          } else {
            alert(data.message || "Error al enviar el comentario.")
          }
        })
        .catch((error) => {
          // Ocultar estado de carga
          comentarioStatus.style.display = "none"
          btnEnviar.disabled = false

          console.error("Error:", error)
          alert("Ha ocurrido un error al enviar el comentario.")
        })
    })
  }

  // Función para manejar la edición de comentarios
  function handleEditComment() {
    const comentarioId = this.getAttribute("data-id")
    const contenidoOriginal = decodeURIComponent(this.getAttribute("data-content"))
    const comentarioElement = this.closest(".comment")
    const comentarioText = comentarioElement.querySelector(".comment__text")

    // Crear formulario de edición
    const formEdit = document.createElement("div")
    formEdit.className = "comment-edit-form"
    formEdit.innerHTML = `
      <textarea class="edit-textarea">${contenidoOriginal}</textarea>
      <div class="edit-actions">
        <button class="btn-save-edit"><i class="fas fa-check"></i> Guardar</button>
        <button class="btn-cancel-edit"><i class="fas fa-times"></i> Cancelar</button>
      </div>
    `

    // Reemplazar el texto con el formulario
    comentarioText.innerHTML = ""
    comentarioText.appendChild(formEdit)

    // Enfocar el textarea
    const textarea = formEdit.querySelector(".edit-textarea")
    textarea.focus()

    // Manejar guardar cambios
    const btnSave = formEdit.querySelector(".btn-save-edit")
    btnSave.addEventListener("click", () => {
      const nuevoContenido = textarea.value.trim()

      if (!nuevoContenido) {
        alert("El comentario no puede estar vacío.")
        return
      }

      // Mostrar indicador de carga
      btnSave.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...'
      btnSave.disabled = true

      fetch("../../backend/gestionRecursos/edit_comment.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `comentario_id=${comentarioId}&contenido=${encodeURIComponent(nuevoContenido)}`,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Actualizar el contenido del comentario
            comentarioText.innerHTML = `<p>${nuevoContenido.replace(/\n/g, "<br>")}</p>`

            // Actualizar el atributo data-content del botón de editar
            const editBtn = comentarioElement.querySelector(".btn-edit-comment")
            if (editBtn) {
              editBtn.setAttribute("data-content", encodeURIComponent(nuevoContenido))
            }
          } else {
            alert(data.message || "Error al actualizar el comentario.")
            comentarioText.innerHTML = `<p>${contenidoOriginal.replace(/\n/g, "<br>")}</p>`
          }
        })
        .catch((error) => {
          console.error("Error:", error)
          alert("Ha ocurrido un error al actualizar el comentario.")
          comentarioText.innerHTML = `<p>${contenidoOriginal.replace(/\n/g, "<br>")}</p>`
        })
    })

    // Manejar cancelar edición
    const btnCancel = formEdit.querySelector(".btn-cancel-edit")
    btnCancel.addEventListener("click", () => {
      comentarioText.innerHTML = `<p>${contenidoOriginal.replace(/\n/g, "<br>")}</p>`
    })
  }

  // Función para manejar la eliminación de comentarios
  function handleDeleteComment() {
    if (confirm("¿Estás seguro de que deseas eliminar este comentario?")) {
      const comentarioId = this.getAttribute("data-id")
      const comentarioElement = this.closest(".comment")

      fetch("../../backend/gestionRecursos/delete_comment.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `comentario_id=${comentarioId}`,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Eliminar el comentario del DOM con animación
            comentarioElement.style.opacity = "0"
            setTimeout(() => {
              comentarioElement.remove()

              // Si no quedan comentarios, mostrar mensaje
              if (commentsContainer && commentsContainer.children.length === 0) {
                commentsContainer.innerHTML = `
                <div class="no-comments">
                  <p>No hay comentarios aún. ¡Sé el primero en comentar!</p>
                </div>
              `
              }
            }, 300)
          } else {
            alert(data.message || "Error al eliminar el comentario.")
          }
        })
        .catch((error) => {
          console.error("Error:", error)
          alert("Ha ocurrido un error al eliminar el comentario.")
        })
    }
  }

  // Asignar eventos a los botones de editar y eliminar existentes
  document.querySelectorAll(".btn-edit-comment").forEach((button) => {
    button.addEventListener("click", handleEditComment)
  })

  document.querySelectorAll(".btn-delete-comment").forEach((button) => {
    button.addEventListener("click", handleDeleteComment)
  })

  // Verificar si hay comentarios y mostrarlos correctamente
  if (commentsContainer) {
    // Si no hay comentarios dentro del contenedor, mostrar el mensaje
    if (commentsContainer.children.length === 0) {
      commentsContainer.innerHTML = `
        <div class="no-comments">
            <p>No hay comentarios aún. ¡Sé el primero en comentar!</p>
        </div>
      `
    }
  }
})

  