const navBtn = document.querySelector('.humburger');
const smallScreenMenu = document.querySelector('.nav-links-small-screen')
// console.log(navBtn)
navBtn.addEventListener('click', ()=>{
    if(navBtn.classList.contains('nav-open')){
        navBtn.classList.remove('nav-open');
        smallScreenMenu.style.transform = 'translateX(-100%)'
    }
    else{
        navBtn.classList.add('nav-open');
        smallScreenMenu.style.transform = 'translateX(0%)'
    }
})