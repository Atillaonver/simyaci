// Define a class named Modal
class byModal {
  // Define a constructor method that takes four parameters
  constructor(title, content, width, height) {
    // Create a div element for the modal
    this.modal = $("<div>").addClass("modal");
    // Create a div element for the modal header
    this.header = $("<div>").addClass("modal-header");
    // Create a span element for the modal title
    this.title = $("<span>").addClass("modal-title").text(title);
    // Create a button element for the modal close
    this.close = $("<button>").addClass("modal-close").text("×");
    // Append the title and the close button to the header
    this.header.append(this.title, this.close);
    // Create a div element for the modal body
    this.body = $("<div>").addClass("modal-body").html(content);
    // Append the header and the body to the modal
    this.modal.append(this.header, this.body);
    // Set the width and height of the modal
    this.modal.css({
      width: width,
      height: height
    });
  }

  // Define a method to show the modal
  show() {
    // Append the modal to the document body
    $("body").append(this.modal);
    // Fade in the modal
    this.modal.fadeIn();
    // Center the modal on the screen
    this.modal.css({
      left: ($(window).width() - this.modal.width()) / 2,
      top: ($(window).height() - this.modal.height()) / 2
    });
  }

  // Define a method to hide the modal
  hide() {
    // Fade out the modal
    this.modal.fadeOut(() => {
      // Remove the modal from the document
      this.modal.remove();
    });
  }
}
