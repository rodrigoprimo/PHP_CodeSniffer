

if (something) print 'hello';

if (something) {
    print 'hello';
} else print 'hi';

if (something) {
    print 'hello';
} else if (something) print 'hi';

for (i; i > 0; i--) print 'hello';

while (something) print 'hello';

do {
    i--;
} while (something);

do i++; while (i < 5);

SomeClass.prototype.for = function() {
    // do something
};

if ($("#myid").rotationDegrees()=='90')
    $('.modal').css({'transform': 'rotate(90deg)'});

if ($("#myid").rotationDegrees()=='90')
    $foo = {'transform': 'rotate(90deg)'};

if (something) {
    alert('hello');
} else /* comment */ if (somethingElse) alert('hi');

if (sniffShouldBailEarly);

if (false) {
} else if (sniffShouldBailEarly);

if (false) {
} else (sniffShouldGenerateError);

if (false) {
} else; // Sniff should bail early.

while (sniffShouldBailEarly);

for (sniffShouldBailEarly; sniffShouldBailEarly > 0; sniffShouldBailEarly--);

do ; while ($sniffShouldBailEarly > 5);
