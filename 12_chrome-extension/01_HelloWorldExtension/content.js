const button = document.createElement('button');
button.innerText = 'Click me!';
button.style.position = 'fixed';
button.style.top = '10px';
button.style.left = '10px';
button.style.zIndex = '9999';
document.body.appendChild(button);

button.addEventListener('click', function() {
  alert('Hello World!');
});
